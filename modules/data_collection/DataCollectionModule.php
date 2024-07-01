<?php

namespace km_message_filter;

class DataCollectionModule extends Module {
    private static $instance;

    private $interval;

    private $max_retries = 5;

    //	private $api_root = 'http://127.0.0.1:8000/api';
    private $api_root = 'https://spamboard.kofimokome.stream/api';

    public function __construct() {
        parent::__construct();
        $this->interval = get_option( 'kmcfmf_collection_interval', 7 );
        if ( get_option( 'kmcfmf_message_auto_delete_toggle' ) === 'on' ) {
            $interval = get_option( 'kmcfmf_message_auto_delete_duration' );
            if ( $interval < $this->interval ) {
                $this->interval = $interval;
                update_option( 'kmcfmf_collection_interval', $interval );
            }
        }
        $is_dev_mode = false;
        if ( defined( 'WP_KMCFMF__DEV_MODE' ) ) {
            $is_dev_mode = WP_KMCFMF__DEV_MODE;
        }
        if ( !$is_dev_mode ) {
            $this->initSync();
        }
        self::$instance = $this;
    }

    /**
     * @since v1.4.9
     * @author kofimokome
     */
    private function initSync() {
        //		if ( kmcf7ms_fs()->is_anonymous() ) {
        $can_sync = get_option( 'kmcfmf_enable_collection', '' ) == 'on';
        // for users who did not accept the freemius policy after installation
        //		} else {
        //			$can_sync = get_option( 'kmcfmf_disable_collection', '' ) != 'on'; // for users who accepted the freemius policy after installation
        //		}
        if ( $can_sync ) {
            $next_sync = get_option( 'kmcfmf_collection_next_sync', 0 );
            $diff = 1;
            if ( $next_sync > 0 ) {
                $now = time();
                $diff = $now - $next_sync;
            }
            //			$diff = ( $now - $last_sync ) / ( 60 * 60 * 24 );
            if ( $diff > 0 ) {
                $this->sync();
            }
        }
    }

    /**
     * @since v1.4.9
     * @author kofimokome
     */
    private function sync() {
        $is_collection_busy = get_option( 'kmcfmf_collection_working', 0 );
        //		if ( $is_collection_busy == 0 ) {
        // check if we are authenticated
        $token = get_option( 'kmcfmf_auth_token', '' );
        if ( $token == '' ) {
            $this->authenticate();
        }
        $syncing_now = get_option( 'kmcfmf_collection_syncing_now', 'spam_words' );
        update_option( 'kmcfmf_collection_status', 'running' );
        if ( $syncing_now == 'spam_words' ) {
            $this->syncSpamWords();
        }
        if ( $syncing_now == 'blocked_messages' ) {
            $this->syncMessages();
        }
        // }
    }

    /**
     * @since v1.5.5
     * @author kofimokome
     */
    private function authenticate() : bool {
        // get website url
        $website_url = get_site_url();
        $url = "/sites/get-token";
        $data = [
            'site'    => $website_url,
            'version' => KMCFMessageFilter::getInstance()->getVersion(),
        ];
        $response = $this->request( $url, 'post', $data );
        if ( $response && isset( $response['token'] ) ) {
            $token = $response['token'];
            update_option( 'kmcfmf_auth_token', $token );
            return $token;
        }
        return false;
    }

    /**
     * @since v1.4.9
     * @author kofimokome
     */
    public static function getInstance() : DataCollectionModule {
        return self::$instance;
    }

    /**
     * @since v1.5.5
     * @author kofimokome
     */
    private function request(
        $url = '',
        $type = 'post',
        $data = [],
        $headers = []
    ) {
        update_option( 'kmcfmf_collection_working', 1 );
        $url = $this->api_root . $url;
        $x = curl_init();
        curl_setopt( $x, CURLOPT_URL, $url );
        if ( $type == 'post' ) {
            curl_setopt( $x, CURLOPT_POST, true );
        }
        if ( sizeof( $data ) > 0 ) {
            $post = http_build_query( $data );
            curl_setopt( $x, CURLOPT_POSTFIELDS, $post );
        }
        if ( sizeof( $headers ) > 0 ) {
            curl_setopt( $x, CURLOPT_HTTPHEADER, $headers );
        }
        //		curl_setopt( $x, CURLOPT_HEADER, true );
        //		curl_setopt( $x, CURLOPT_FAILONERROR, true );
        curl_setopt( $x, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $x, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $x, CURLOPT_TIMEOUT, 4 );
        $y = curl_exec( $x );
        $http_code = curl_getinfo( $x, CURLINFO_HTTP_CODE );
        if ( $http_code < 300 ) {
            $response = json_decode( $y, true );
            curl_close( $x );
            update_option( 'kmcfmf_collection_working', 0 );
            return $response;
        }
        if ( $http_code == 403 ) {
            update_option( 'kmcfmf_auth_token', '' );
        }
        curl_close( $x );
        update_option( 'kmcfmf_collection_working', 0 );
        return false;
    }

    /**
     * @since v1.5.5
     * @author kofimokome
     */
    private function syncSpamWords() {
        $spam_words = sanitize_text_field( get_option( 'kmcfmf_restricted_words', '' ) );
        $spam_emails = sanitize_text_field( get_option( 'kmcfmf_restricted_emails', '' ) );
        if ( trim( $spam_words ) != '' ) {
            $token = get_option( 'kmcfmf_auth_token', '' );
            $url = "/sites/spam-words";
            $data = [
                'words'  => $spam_words,
                'emails' => $spam_emails,
            ];
            $headers = ["Authorization: Bearer {$token}"];
            $response = $this->request(
                $url,
                'post',
                $data,
                $headers
            );
            // send message to server
            if ( $response ) {
                update_option( 'kmcfmf_collection_retries', 0 );
                update_option( 'kmcfmf_collection_syncing_now', 'blocked_messages' );
            } else {
                $retries = get_option( 'kmcfmf_collection_retries', 0 );
                if ( $retries < $this->max_retries ) {
                    $retries++;
                    update_option( 'kmcfmf_collection_retries', $retries );
                } else {
                    update_option( 'kmcfmf_collection_retries', 0 );
                    update_option( 'kmcfmf_collection_syncing_now', 'blocked_messages' );
                }
            }
        } else {
            update_option( 'kmcfmf_collection_retries', 0 );
            update_option( 'kmcfmf_collection_syncing_now', 'blocked_messages' );
        }
        // add 15 minutes to the current time for the next sync
        $next_sync = strtotime( '+15 minutes' );
        update_option( 'kmcfmf_collection_next_sync', $next_sync );
    }

    /**
     * @since v1.5.5
     * @author kofimokome
     */
    private function syncMessages() {
        $last_id_synced = get_option( 'kmcfmf_collection_last_id_synced', 0 );
        $messages = Message::select( 'id, message' )->where( 'id', '>', $last_id_synced )->orderBy( 'id', 'asc' )->take( 50 );
        // update count
        if ( ($size = sizeof( $messages )) > 0 ) {
            // send message to server
            if ( $this->uploadData( $messages ) ) {
                $last_id_synced = $messages[$size - 1]->id;
                update_option( 'kmcfmf_collection_retries', 0 );
                update_option( 'kmcfmf_collection_last_id_synced', $last_id_synced );
                if ( $size < 50 ) {
                    update_option( 'kmcfmf_collection_last_sync', time() );
                    $next_sync = strtotime( "+{$this->interval} days" );
                    update_option( 'kmcfmf_collection_next_sync', $next_sync );
                    update_option( 'kmcfmf_collection_status', 'not_running' );
                    update_option( 'kmcfmf_collection_syncing_now', 'spam_words' );
                } else {
                    // add 15 minutes to the current time
                    $next_sync = strtotime( '+15 minutes' );
                    update_option( 'kmcfmf_collection_next_sync', $next_sync );
                }
            } else {
                $retries = get_option( 'kmcfmf_collection_retries', 0 );
                if ( $retries < $this->max_retries ) {
                    $retries++;
                    update_option( 'kmcfmf_collection_retries', $retries );
                    // add 15 minutes to the current time
                    $next_sync = strtotime( '+15 minutes' );
                    update_option( 'kmcfmf_collection_next_sync', $next_sync );
                } else {
                    update_option( 'kmcfmf_collection_retries', 0 );
                    update_option( 'kmcfmf_collection_last_sync', time() );
                    $next_sync = strtotime( "+{$this->interval} days" );
                    update_option( 'kmcfmf_collection_status', 'not_running' );
                    update_option( 'kmcfmf_collection_next_sync', $next_sync );
                    update_option( 'kmcfmf_collection_syncing_now', 'spam_words' );
                }
            }
        } else {
            update_option( 'kmcfmf_collection_retries', 0 );
            update_option( 'kmcfmf_collection_last_sync', time() );
            $next_sync = strtotime( "+{$this->interval} days" );
            update_option( 'kmcfmf_collection_status', 'not_running' );
            update_option( 'kmcfmf_collection_next_sync', $next_sync );
            update_option( 'kmcfmf_collection_syncing_now', 'spam_words' );
        }
    }

    /**
     * @since v1.5.5
     * @author kofimokome
     */
    private function uploadData( $messages ) : bool {
        $messages = array_map( function ( $message ) {
            return $message->message;
        }, $messages );
        $token = get_option( 'kmcfmf_auth_token', '' );
        $url = "/sites/spam-messages";
        $data = [
            'messages' => $messages,
        ];
        $headers = ["Authorization: Bearer {$token}"];
        $response = $this->request(
            $url,
            'post',
            $data,
            $headers
        );
        // send message to server
        if ( $response ) {
            return true;
        }
        return false;
    }

    /**
     * @since v1.5.5
     * @author kofimokome
     */
    public function dismissDataCollectionNotice() {
        $accept = ( isset( $_POST['accept'] ) ? sanitize_text_field( $_POST['accept'] ) : 'no' );
        if ( $accept == 'yes' ) {
            update_option( 'kmcfmf_enable_collection', 'on' );
        }
        $next_notice = strtotime( '+30 days' );
        update_option( 'kmcfmf_data_collection_next_notice', $next_notice );
        wp_send_json_success();
        wp_die();
    }

    protected function addActions() {
        parent::addActions();
        // ajax reques
        add_action( 'wp_ajax_kmcfmf_dismiss_data_collection_notice', [$this, 'dismissDataCollectionNotice'] );
    }

}
