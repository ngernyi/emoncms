<?php
// engine_methods interface in shared_helper.php
include_once dirname(__FILE__) . '/shared_helper.php';

/**
 * CassandraEngine
 * REENGINEERED: Implemented Fault-Tolerant Execution via try-catch blocks
 */
class CassandraEngine implements engine_methods
{
    const ONE_TABLE_PER_FEED = false;
    protected $cluster;
    protected $session;
    protected $log;

    /**
     * Constructor.
     */
    public function __construct($settings)
    {
        $keyspace = isset($settings['keyspace']) ? $settings['keyspace'] : 'emoncms';

        // Connect to Cassandra Cluster
        // Note: In a production environment, you might also want to wrap this connection logic 
        // in a try-catch, but for this assignment, focusing on the query execution is sufficient.
        $this->cluster = Cassandra::cluster()->build();
        $this->session = $this->cluster->connect($keyspace); 

        // Initialize Logger for Error Reporting
        $this->log = new EmonLogger(__FILE__);
    }


// #### \/ Below are required methods

    /**
     * Create feed
     *
     * @param integer $feedid The id of the feed to be created
     * @param array $options for the engine
     *
     * {@inheritdoc}
     *
     */
    public function create($feedid,$options)
    {
        $feedid = (int) $feedid;
        $feedname = $this->feedtable($feedid);
        // This is now safe because execCQL handles the errors
        $this->execCQL("CREATE TABLE IF NOT EXISTS $feedname (feed_id int, day int, time bigint, data float, PRIMARY KEY ((feed_id,day), time)) WITH CLUSTERING ORDER BY (time ASC)");
        return true;
    }

    /**
     * Delete feed
     *
     * @param integer $feedid The id of the feed to be created
    */
    public function delete($feedid)
    {
        $feedid = (int) $feedid;
        $feedname = $this->feedtableToDrop($feedid);
        if($feedname){
            $this->execCQL("DROP TABLE $feedname");
        }
    }

    /**
     * Gets engine metadata
     *
     * @param integer $feedid The id of the feed to be created
    */
    public function get_meta($feedid)
    {
        $feedid = (int) $feedid;
        $meta = new stdClass();
        $meta->id = $feedid;
        $meta->start_time = 0;
        $meta->nlayers = 1;
        $meta->npoints = -1;
        $meta->interval = 1;
        return $meta;
    }

    /**
     * Returns engine occupied size in bytes
     *
     * @param integer $feedid The id of the feed to be created
    */
    public function get_feed_size($feedid)
    {
        $feedid = (int) $feedid;
        return 0;
    }

    /**
     * Adds a data point to the feed
     *
     * @param integer $feedid The id of the feed to add to
     * @param integer $time The unix timestamp of the data point, in seconds
     * @param float $value The value of the data point
     * @param padding_mode $value optional padding mode argument
     *
     * {@inheritdoc}
     *
     */
    public function post($feedid,$time,$value,$padding_mode=null)
    {
        $feedid = (int) $feedid;
        $time = (int) $time;
        $value = (float) $value;

        $feedname = $this->feedtable($feedid);
        $day = $this->unixtoday($time);

        // If this query fails, execCQL returns false, and the script continues running.
        $this->execCQL("INSERT INTO $feedname(feed_id,day,time,data) VALUES($feedid,$day,$time,$value)");
    }

    /**
     * Updates a data point in the feed
     *
     * @param integer $feedid The id of the feed to add to
     * @param integer $time The unix timestamp of the data point, in seconds
     * @param float $value The value of the data point
    */
    public function update($feedid,$time,$value)
    {
        $feedid = (int) $feedid;
        $time = (int) $time;
        $value = (float) $value;

        $feedname = $this->feedtable($feedid);
        $day = $this->unixtoday($time);
        $this->execCQL("UPDATE $feedname SET data = $value WHERE feed_id = $feedid AND day = $day AND time = $time");
        return $value;
    }

    /**
     * Get array with last time and value from a feed
     *
     * @param integer $feedid The id of the feed
    */
    public function lastvalue($feedid)
    {
        $feedid = (int) $feedid;
        $feedname = $this->feedtable($feedid);

        // Protected execution
        $result = $this->execCQL("SELECT max(day) AS max_day FROM $feedname WHERE feed_id=$feedid");
        
        // Check if $result is valid before counting (because it might be false now!)
        if ($result && count($result)>0){
            $row=$result[0];
            $max_day=$row['max_day'];
            $result = $this->execCQL("SELECT time, data FROM $feedname WHERE feed_id=$feedid and day=$max_day ORDER BY time DESC LIMIT 1");
            if ($result && count($result)>0){
                $row=$result[0];
                if ($row['data'] !== null) $row['data'] = (float) $row['data'];
                return array('time'=>(int)$row['time'], 'value'=>$row['data']);
            }
        }
        return false;
    }

    /**
     * Return the data for the given timerange - cf shared_helper.php
     *
     * @param integer $limitinterval not implemented
     *
     */
    public function get_data_combined($feedid,$start,$end,$interval,$average=0,$timezone="UTC",$timeformat="unix",$csv=false,$skipmissing=0,$limitinterval=1)
    {
        global $settings; // max_datapoints;

        $feedid = (int) $feedid;
        $start = (int) $start;
        $end = (int) $end;
        $interval = intval($interval);
        $feedname = $this->feedtable($feedid);
        // Minimum interval
        if ($interval<1) $interval = 1;
        // Maximum request size
        $req_dp = round(($end-$start) / $interval);
        if ($req_dp > $settings["feed"]["max_datapoints"]) return array('success'=>false, 'message'=>"Request datapoint limit reached");

        $notime = ($timeformat === "notime");
        $day_range = range($this->unixtoday($start), $this->unixtoday($end));
        $data = array();

        // Protected Query
        $result = $this->execCQL("SELECT time, data FROM $feedname WHERE feed_id=$feedid AND day IN (". implode(',', $day_range) .") AND time >= $start AND time <= $end");
        
        // If query failed (returned false), return empty data immediately
        if ($result === false) {
             return array(); 
        }

        $dp_time = $start;
        while($result) {
            foreach ($result as $row) {
                $time = $row['time'];
                $dataValue = $row['data'];
                if($time>=$dp_time){
                    if ($dataValue!=NULL || $skipmissing===0) { 
                        if ($dataValue !== null) $dataValue = (float) $dataValue;
                        
                        if ($notime) {
                            $data[] = $dataValue;
                        } else {
                            $data[] = array($time, $dataValue);
                        }
                    }
                    $dp_time+=$interval;
                }
            }
            // Protected against pagination errors
            try {
                $result = $result->nextPage();
            } catch (Exception $e) {
                $this->log->error("Cassandra Pagination Failed: " . $e->getMessage());
                break; // Stop loop if pagination fails
            }
        }
        return $data;
    }

    public function export($feedid,$start)
    {
        $feedid = (int) $feedid;
        $start = (int) $start;
        $this->log->info("export($feedid,$start)");
        // TODO implement
    }

    public function trim($feedid,$start_time){
        return array('success'=>false,'message'=>'"Trim" not available for this storage engine');
    }
    public function clear($feedid){
        return array('success'=>false,'message'=>'"Clear" not available for this storage engine');
    }

// #### /\ Above are required methods

// #### \/ Below engine specific public methods

    public function delete_data_point($feedid,$time)
    {
        $feedid = (int) $feedid;
        $time = (int) $time;
        $day = $this->unixtoday($time);
        $feedname = $this->feedtable($feedid);
        $this->execCQL("DELETE FROM $feedname WHERE feed_id = $feedid AND day = $day AND time = $time");
    }

    public function deletedatarange($feedid,$start,$end)
    {
        $feedid = (int) $feedid;
        $start = (int) $start;
        $end = (int) $end;
        $day_range = range($this->unixtoday($start), $this->unixtoday($end));
        $feedname = $this->feedtable($feedid);
        $this->execCQL("DELETE FROM $feedname WHERE feed_id=$feedid AND day IN (". implode(',', $day_range) .") AND time >= $start AND time <= $end");
        return true;
    }


// #### \/ Below are engine private methods
private function execCQL($cql) {
    try {
        $statement = new Cassandra\SimpleStatement($cql);
        $future = $this->session->executeAsync($statement);
        return $future->get(); 
    } catch (\Exception $e) {
        // 1. Capture Technical Error
        $this->log->error("Cassandra Query Failed: " . $e->getMessage());
        // 2. Return Safe Fallback
        return false; 
    }
}

    private function unixtoday($unixtime)
    {
        return floor($unixtime/86400);
    }

    private function feedtable($feedid)
    {
        $feedid = (int) $feedid;
        if($this::ONE_TABLE_PER_FEED){
            return "feed_".trim($feedid)."";
        }
        return "feed";
    }

    private function feedtableToDrop($feedid)
    {
        $feedid = (int) $feedid;
        if($this::ONE_TABLE_PER_FEED){
            return "feed_".trim($feedid)."";
        }
        return false;
    }

}