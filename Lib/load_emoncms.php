<?php
/*

All Emoncms code is released under the GNU Affero General Public License.
See COPYRIGHT.txt and LICENSE.txt.

---------------------------------------------------------------------
Emoncms - open source energy visualisation
Part of the OpenEnergyMonitor project:
http://openenergymonitor.org

*/

define('EMONCMS_EXEC', 1);
chdir(dirname(__FILE__)."/..");
require "process_settings.php";
require_once "Lib/Config.php";
Config::load(dirname(__FILE__)."/../settings.ini");
require "Lib/EmonLogger.php";
$log = new EmonLogger(__FILE__);

// Connect to mysql
$mysqli = @new mysqli(
    Config::get('sql', 'server', 'localhost'),
    Config::get('sql', 'username', 'root'),
    Config::get('sql', 'password', ''),
    Config::get('sql', 'database', 'emoncms'),
    Config::get('sql', 'port', 3306)
);

if ($mysqli->connect_error) { 
    $log->error("Cannot connect to MYSQL database:". $mysqli->connect_error);
    die('Check log\n');
}
// Connect to redis
if (Config::get_bool('redis', 'enabled', false)) {
    $redis = new Redis();
    $redis_host = Config::get('redis', 'host', 'localhost');
    $redis_port = Config::get('redis', 'port', 6379);
    if (!$redis->connect($redis_host, $redis_port)) {
        $log->error("Cannot connect to redis at ".$redis_host.":".$redis_port);  die('Check log\n');
    }
    $redis_prefix = Config::get('redis', 'prefix');
    if (!empty($redis_prefix)) $redis->setOption(Redis::OPT_PREFIX, $redis_prefix);
    $redis_auth = Config::get('redis', 'auth');
    if (!empty($redis_auth)) {
        if (!$redis->auth($redis_auth)) {
            $log->error("Cannot connect to redis at ".$redis_host.", authentication failed"); die('Check log\n');
        }
    }
} else {
    $redis = false;
}
// Default userid 
$userid = 1;

require("Modules/user/user_model.php");
$user = new User($mysqli,$redis,null);

require_once "Modules/feed/feed_model.php";
$feed = new Feed($mysqli,$redis,Config::get('feed'));
