<?php

namespace km_message_filter;

class Filter
{
    /**
     * Runs spam filter on text fields and text area fields
     * @since 1.4.0
     */
    public function validateTextField( $message )
    {
        $found = false;
        $check_words = explode( ',', get_option( 'kmcfmf_restricted_words' ) );
        // we separate words with spaces and single words and treat them different.
        $check_words_with_spaces = array_filter( $check_words, function ( $word ) {
            return preg_match( "/\\s+/", $word );
        } );
        $check_words = array_values( array_diff( $check_words, $check_words_with_spaces ) );
        // UnderWordPressue: make all lowercase - safe is safe
        $message = strtolower( $message );
        foreach ( $check_words_with_spaces as $check_word ) {
            if ( preg_match( "/\\b" . $check_word . "\\b/", $message ) ) {
                return $check_word;
            }
        }
        // still not found a spam?, we continue with the check for single words
        // UnderWordPressue: Change explode(" ", $values) to preg_split([white-space]) -  reason: whole whitespace range are valid separators
        //                   and rewrite the foreach loops
        $values = preg_split( '/\\s+/', $message );
        foreach ( $values as $value ) {
            $value = trim( $value );
            foreach ( $check_words as $check_word ) {
                /*if (preg_match("/^\.\w+/miu", $value) > 0) {
                			$found = true;
                		}else if (preg_match("/\b" . $check_word . "\b/miu", $value) > 0) {
                			$found = true;
                		}*/
                $check_word = strtolower( trim( $check_word ) );
                switch ( $check_word ) {
                    case '':
                        break;
                    case '[russian]':
                        $found = preg_match( '/[а-яА-Я]/miu', $value );
                        break;
                    case '[hiragana]':
                        break;
                    case '[katakana]':
                        break;
                    case '[kanji]':
                        break;
                    case '[japanese]':
                        break;
                    case '[link]':
                        $pattern = '/((ftp|http|https):\\/\\/\\w+)|(www\\.\\w+\\.\\w+)/ium';
                        // filters http://google.com and http://www.google.com and www.google.com
                        $found = preg_match( $pattern, $value );
                        break;
                    case '[emoji]':
                        break;
                    default:
                        $like_start = preg_match( '/^\\*/', $check_word );
                        $like_end = preg_match( '/\\*$/', $check_word );
                        # Remove leading and trailing asterisks from $check_word
                        $regex_pattern = preg_quote( trim( $check_word, '*' ), '/' );
                        if ( $like_start ) {
                            $regex_pattern = '.*' . $regex_pattern;
                        }
                        if ( $like_end ) {
                            $regex_pattern = $regex_pattern . '+.*';
                        }
                        
                        if ( $like_end || $like_start ) {
                            $found = preg_match( '/^' . $regex_pattern . '$/miu', $value );
                        } else {
                            
                            if ( $this->hasEmoji( $check_word ) ) {
                                // if the checkword is an emoji
                                $found = strpos( $message, $check_word ) !== false;
                            } else {
                                $found = preg_match( '/\\b' . $regex_pattern . '\\b/miu', $value );
                            }
                        
                        }
                        
                        break;
                }
                if ( $found ) {
                    return $check_word;
                }
            }
            // end of foreach($checkwords)
        }
        // end of foreach($values...)
        #####################
        # Final evaluation. #
        #####################
        return false;
    }
    
    /**
     * Checks if text has an emoji
     * @since v1.3.6
     */
    private function hasEmoji( $emoji )
    {
        $unicodeRegexp = '([*#0-9](?>\\xEF\\xB8\\x8F)?\\xE2\\x83\\xA3|\\xC2[\\xA9\\xAE]|\\xE2..(\\xF0\\x9F\\x8F[\\xBB-\\xBF])?(?>\\xEF\\xB8\\x8F)?|\\xE3(?>\\x80[\\xB0\\xBD]|\\x8A[\\x97\\x99])(?>\\xEF\\xB8\\x8F)?|\\xF0\\x9F(?>[\\x80-\\x86].(?>\\xEF\\xB8\\x8F)?|\\x87.\\xF0\\x9F\\x87.|..(\\xF0\\x9F\\x8F[\\xBB-\\xBF])?|(((?<zwj>\\xE2\\x80\\x8D)\\xE2\\x9D\\xA4\\xEF\\xB8\\x8F\\k<zwj>\\xF0\\x9F..(\\k<zwj>\\xF0\\x9F\\x91.)?|(\\xE2\\x80\\x8D\\xF0\\x9F\\x91.){2,3}))?))';
        
        if ( preg_match( $unicodeRegexp, $emoji ) ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    /**
     * Runs spam filter on email fields
     * @since v1.4.0
     */
    public function validateEmail( $value )
    {
        $check_words = ( strlen( trim( get_option( 'kmcfmf_restricted_emails' ) ) ) > 0 ? explode( ",", get_option( 'kmcfmf_restricted_emails' ) ) : [] );
        foreach ( $check_words as $check_word ) {
            if ( preg_match( "/\\b" . $check_word . "\\b/", $value ) ) {
                return true;
            }
        }
        return false;
    }

}