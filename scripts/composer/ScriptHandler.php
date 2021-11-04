<?php

/**
 * @file
 * Contains \DrupalProject\composer\ScriptHandler.
 */

namespace DrupalProject\composer;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;

class ScriptHandler {
  
  private static function getPaths() {
    $paths = array();
    
    // The cPanel account home directory path
    $paths['homeDir'] = getenv("HOME") . '/';
  
    // The public_html path for the cPanel account
    $paths['publicHTML'] = $paths['homeDir'] . 'public_html/';
    
    // The composer project root
    $paths['projectRoot'] = getcwd() . '/';
  
    // The docroot path for the ILFW
    $paths['docRoot'] = $paths['projectRoot'] . 'docroot/';
  
    return $paths;
  }
  
  private static function getCpanelUser() {
    return getenv('USER');
  }
  
  private static function getDbUser() {
    return self::getCpanelUser() . '_ilfw';
  }
  
  private static function getDbName() {
    return self::getCpanelUser() . '_ilfw';
  }
  
  /**
   * Returns an empty array if db info can't be found
   * Otherwise, returns array in format
   * dbinfo['dbUser']
   * dbinfo['dbPass']
  **/
  private static function getDbInfo() {
    $paths = self::getPaths();
    $dbinfo = array();
    
    if( file_exists($paths['homeDir'] . '.my.cnf') ) {
        $myCnfInfo = parse_ini_file($paths['homeDir'] . '.my.cnf', TRUE);
        $dbinfo['dbUser'] = $myCnfInfo['client']['user'];
        $dbinfo['dbPass'] = $myCnfInfo['client']['password'];
    }
    
    return $dbinfo;
  }
  
  private static function createMyCnf($dbUser, $dbPass) {
    $paths = self::getPaths();
    
    $fileContent = "[client]\nuser=$dbUser\npassword=\"$dbPass\"\n";
    
    file_put_contents($paths['homeDir'] . '.my.cnf', $fileContent);
    chmod($paths['homeDir'] . '.my.cnf', 0600);
  }

  public static function installSite(Event $event) {
    $paths = self::getPaths();
    $dbInfo = self::getDbInfo();
    
    $install_status = shell_exec($paths['projectRoot'] . 'vendor/drush/drush/drush status bootstrap --pipe --format=list');
    if( $install_status != 'Successful' ) {
        if( !empty($dbInfo) ) {
            $drushCommand = "site:install --yes --site-name=IllinoisFramework --db-url=\"mysql://" . $dbInfo['dbUser'] . ":" . $dbInfo['dbPass'] . "@localhost/" . self::getDbName(). "\"";
            shell_exec($paths['projectRoot'] . 'vendor/drush/drush/drush ' . $drushCommand);
        }
        else {
            echo 'Can\'t read DB info for site install';
        }
    }
    else {
        echo 'Drupal site already installed';
    }
  }
  
  public static function createSymlinks(Event $event) {
    $paths = self::getPaths();
    
    $dir = new \DirectoryIterator($paths['docRoot']);
    
    foreach ($dir as $fileinfo) {
        if(!$fileinfo->isDot()) {
            try {
                symlink($fileinfo->getPathname(), $paths['publicHTML'] . $fileinfo->getFilename());
                echo "Create symlink " . $fileinfo->getPathname() . "\n";
            } 
            catch (\Throwable $t) {
                echo 'Symlink already exists for ' . $fileinfo->getFilename() . "\n";
            }
        }
    }
    
    // Symlink vendor directory to project root
    /*
    try {
        symlink($projectRoot . '/vendor', $homeDir . '/vendor');
        echo "Create symlink ~/vendor" . "\n";
    } 
    catch (\Throwable $t) {
        echo 'Symlink already exists for ~/vendor' . "\n";
    }
    */
  }
  
  public static function createMySQLuser(Event $event) {
    $paths = self::getPaths();
    $dbPass = '';
    
    // Check if ~/.my.cnf file exists
    if( !file_exists($paths['homeDir'] . '.my.cnf') ) {
        // Check if mysql user already exists
        $apiResult = json_decode(shell_exec('cpapi2 --output=json MysqlFE dbuserexists dbuser=' . self::getDbUser()));
        $userExists = $apiResult->cpanelresult->data[0];

        if( !$userExists ) {
            $dbPass = bin2hex(random_bytes(10));
            $apiResult = json_decode(shell_exec('cpapi2 --output=json MysqlFE createdbuser dbuser=' . self::getDbUser() . ' password=' . $dbPass));
            self::createMyCnf(self::getDbUser(), $dbPass);
            echo "Created database user self::getDbUser - credentials stored in ~/.my.cnf\n";
        }
        else {
            echo "DB user already exists \n";
        }
    }
    else {
        echo '~/.my.cnf file already exists' . "\n";
    }

  }

  public static function createMySQLdb(Event $event) {
    $paths = self::getPaths();
    
    $createDBResult = json_decode(shell_exec('cpapi2 --output=json MysqlFE createdb db=' . self::getDbName()));
    
    //var_dump($createDBResult);
    
    if( $createDBResult->cpanelresult->event->result == 0 ) {
        echo "DB creation failed. Likely already exists.\n";
    }
    else {
        echo "Created database " . self::getDbName();
    }
    
    $permResult = json_decode(shell_exec('cpapi2 --output=json MysqlFE setdbuserprivileges privileges=\'ALL PRIVILEGES\' db=' . self::getDbName() . ' dbuser=' . self::getDbUser()));
    
    //var_dump($permResult);
    
    if( $permResult->cpanelresult->event->result == 0 ) {
        echo "ERROR setting database permissions\n";
    }
    else {
        echo "Set DB permissions for the DB user\n";
    }

  }

}
