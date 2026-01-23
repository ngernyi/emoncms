<?php
/*
 All Emoncms code is released under the GNU Affero General Public License.
 See COPYRIGHT.txt and LICENSE.txt.

 ---------------------------------------------------------------------
 Emoncms - open source energy visualisation
 Part of the OpenEnergyMonitor project:
 http://openenergymonitor.org
 */

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

class EmonLogger
{
    private $logfile = "";
    private $caller = "";
    private $logenabled = false;
    private $log_level = 2;
    public $stout = false;

    private $log_levels = array(
            1 =>'INFO',
            2 =>'WARN', // default
            3 =>'ERROR'
        );

    public function __construct($clientFileName)
    {
        // Refactored to use the Config class
        if (!Config::get_bool('log', 'enabled', false)) {
            $this->logenabled = false;
            return;
        }

        $this->logfile = Config::get('log', 'location', '/var/log/emoncms') . "/emoncms.log";
        $this->log_level = Config::get('log', 'level', 2); // Default to WARN
        $this->caller = basename($clientFileName);

        if (!file_exists($this->logfile)) {
            $fh = @fopen($this->logfile, "a");
            if (!$fh) {
               error_log("Log file could not be created at " . $this->logfile);
            } else {
               @fclose($fh);
            }
        }
        $this->logenabled = is_writable($this->logfile);
    }
    
    public function set($logfile, $log_level)
    {
        $this->logfile = $logfile;
        $this->logenabled = true;
        $this->log_level = $log_level;
    }

    public function info($message)
    {
        if ($this->log_level <= 1) {
            $this->write("INFO", $message);
        }
    }

    public function warn($message)
    {
        if ($this->log_level <= 2) {
            $this->write("WARN", $message);
        }
    }

    public function error($message)
    {
        if ($this->log_level <= 3) {
            $this->write("ERROR", $message);
        }
    }

    public function levels()
    {
        return $this->log_levels;
    }

    private function write($type, $message)
    {
        if (!$this->logenabled) {
            return;
        }
        
        if ($this->stout) {
            print $type." ".$message."\n";
        }

        $now = microtime(true);
        $micro = sprintf("%03d", ($now - round($now,0,PHP_ROUND_HALF_DOWN)) * 1000);
        $now = DateTime::createFromFormat('U', (int)$now); // Only use UTC for logs
        $now = $now->format("Y-m-d H:i:s").".$micro";
        // Clear log file if more than 256MB (temporary solution)
        if (filesize($this->logfile)>(1024*1024*256)) {
            $fh = @fopen($this->logfile, "w");
            @fclose($fh);
        }
        if ($fh = @fopen($this->logfile, "a")) {
            @fwrite($fh, $now."|$type|$this->caller|".$message."\n");
            @fclose($fh);
        }
    }
}
