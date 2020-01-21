<?php
/**
 * Plugin Name: Delete Pending Comments that Match Blacklisted Words
 * Description: Reads the blacklist, and deletes any pending comment matching the words. Moves spammy comments to spam.
 * Author: John Kawakami
 * Version: 0.1
 * Requires at least: 5
 * Requires PHP: 7.3
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists('DPCMBW') ) {

class DPCMBW {

    static $instance = false;

    private function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    public static function getInstance() {
        if (! self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function admin_menu() {
        add_submenu_page( 'edit-comments.php', 'Delete Comments Matching Blacklist', 
            'Delete Comments Matching Blacklist', 'moderate_comments', 
            'dpcmbw', array( $this, 'delete_pending_for_all_words') );
        add_submenu_page( 'edit-comments.php', 'Move Spam Peers to Spam', 
            'Move Spam Peers to Spam', 'moderate_comments', 
            'msp', array( $this, 'move_spam_peers') );
    }


    public static function delete_pending_matching( $word = null ) {

        global $wpdb;

        if ($word===null or preg_match('/^\\s+$/', $word)) return;

        $query=$wpdb->prepare("DELETE FROM `{$wpdb->prefix}comments` WHERE `comment_approved` = '0' 
                            AND comment_content LIKE %s 
                            OR comment_content LIKE %s 
                            OR comment_content LIKE %s 
                            OR comment_content LIKE %s 
                            OR comment_content LIKE %s 
                            OR comment_content LIKE %s 
                            OR comment_content LIKE %s",
                            "% $word %", 
                            "$word %", 
                            "% $word",
                            "%>$word ",
                            " $word<%",
                            "% $word\n",
                            "%>$word<%"
                            );
        $response=$wpdb->query($query);
        if($response) {
            echo "Deleted comments containing $word<br />";
        } else {
            echo ". ";
        }
    }

    public static function delete_evil_urls() {
        global $wpdb;
        $query = $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}comments` WHERE comment_author_url LIKE '%stmap_%'" );
        $wpdb->query($query);
    }

    public static function delete_pending_for_all_words() {
        $keys = get_option( 'blacklist_keys' );
        $keys = explode("\n", $keys);
        foreach( $keys as $word ) {
            $this->delete_pending_matching( $word );
        }
        $this->delete_evil_urls();
        echo "<div class='updated'><p>Completed deletion.</p></div>";
        // fixme - load comment list here
    }

    /**
     * Scan the spam messages for usernames
     * and move identical pending posts into spam.
     */
    public static function move_spam_peers() {
        echo "<p>Processing Authors</p>";
        flush();
        $this->move_spam_peers_helper( 'comment_author' );
        echo "<p>Processing Author URLs</p>";
        flush();
        $this->move_spam_peers_helper( 'comment_author_url' );
        echo "<p>Processing Author Emails</p>";
        flush();
        $this->move_spam_peers_helper( 'comment_author_email' );
        echo "<div class='updated'><p>Completed moving spam.</p></div>";
        // fixme - load the comment approval page
    }

    private function move_spam_peers_helper( $field ) {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT $field FROM `{$wpdb->prefix}comments` WHERE comment_approved='spam'" );
        $result = $wpdb->get_results( $query );
        $names = array_map( function($a) { return $a->comment_author; }, $result );

        foreach($names as $name) {
            if ($name == '') continue; // skip empties - most people leave URL fields empty
            $query = $wpdb->prepare( "UPDATE `{$wpdb->prefix}comments` SET comment_approved='spam' WHERE comment_approved='0' AND $field=%s",
                $name);
            $response = $wpdb->query( $query );
            if ($response) {
                echo "Moved $name into spam.<br />";
            }
        }
    }
}

$dpcmbw = DPCMBW::getInstance();

} // if ! class_exists
