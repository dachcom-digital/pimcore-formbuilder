<?php

namespace Formbuilder\Tool;

class Placeholder {

    /**
     * @var array
     */
    private static $allowedTags = ['snippet', 'document', 'asset'];

    /**
     * @param string $string
     *
     * @return mixed|string
     */
    public static function parse( $string = '' )
    {
        if ( strpos( $string, '[' ) === FALSE )
        {
            return $string;
        }

        $pattern = self::getRegex();
        return preg_replace_callback( "/$pattern/", [__CLASS__, 'parseSquareBracketsTag'], $string );

    }

    /**
     * @param $m
     *
     * @return string
     */
    private static function parseSquareBracketsTag( $m )
    {
        if ( $m[1] == '[' && $m[6] == ']' )
        {
            return substr($m[0], 1, -1);
        }

        $tag = $m[2];

        $content = isset( $m[5] ) ? $m[5] : null;
        $attributes = self::parseSquareBracketsAttributes( $m[3] );

        $parsedContent = self::parseContent( $tag, $content, $attributes );

        return $m[1] . $parsedContent . $m[6];
    }

    /**
     * @param $tag
     * @param $content
     * @param $attributes
     *
     * @return string
     */
    private static function parseContent( $tag, $content, $attributes )
    {
        if( !isset( $attributes['id'] ) )
        {
            return $content;
        }

        $id = (int) $attributes['id'];

        switch( $tag )
        {
            case 'snippet':

                $params = [
                    'tag'       => $tag,
                    'class'     => 'formbuilder-label-link',
                    'data-id'   => $id,
                    'data-type' => 'snippet',
                    'target'    => '_self',
                    'href'      => '#',
                    'content'   => $content
                ];

                break;

            case 'document':

                $document = \Pimcore\Model\Document::getById( (int) $attributes['id'] );
                $documentFullPath = NULL;

                if( $document instanceof \Pimcore\Model\Document)
                {
                    $documentFullPath = $document->getFullPath();
                }

                $params = [
                    'tag'       => $tag,
                    'class'     => 'formbuilder-label-link',
                    'data-id'   => $id,
                    'data-type' => 'document',
                    'target'    => '_self',
                    'href'      => $documentFullPath,
                    'content'   => $content
                ];

                break;

            case 'asset':

                $asset = \Pimcore\Model\Asset::getById( (int) $attributes['id'] );
                $assetFullPath = NULL;

                if( $asset instanceof \Pimcore\Model\Asset)
                {
                    $assetFullPath = $asset->getFullPath();
                }

                $params = [
                    'tag'       => $tag,
                    'class'     => 'formbuilder-label-link',
                    'data-id'   => $id,
                    'data-type' => 'asset',
                    'target'    => '_blank',
                    'href'      => $assetFullPath,
                    'content'   => $content
                ];

                break;

            default:
                $params = [];
        }

        //allow third parties to manipulate link!
        if( !empty( $params ) )
        {
            $cmdEv = \Pimcore::getEventManager()->trigger('formbuilder.form.label.placeholder', NULL, ['params' => $params]);

            if ($cmdEv->stopped())
            {
                $eventParams = $cmdEv->last();

                if( is_array( $eventParams ) )
                {
                    $params = array_merge( $params, $eventParams );
                }
            }
        }

        $link = '<a ' . self::arrayToAttributes($params) . '>' . $params['content'] . '</a>';

        return $link;
    }

    /**
     * @param $params
     *
     * @return string
     */
    private static function arrayToAttributes( $params )
    {
        $pairs = [];
        $disallowedAttr = ['content', 'tag'];

        foreach ( $params as $name => $value )
        {
            if( in_array( $name, $disallowedAttr ) )
            {
                continue;
            }

            $name = htmlentities($name, ENT_QUOTES, 'UTF-8');
            $value = htmlentities($value, ENT_QUOTES, 'UTF-8');

            if ( is_bool($value) )
            {
                if ($value)
                {
                    $pairs[] = $name;
                }
            }
            else
            {
                $pairs[] = sprintf('%s="%s"', $name, $value);
            }
        }

        return join(' ', $pairs);

    }

    /**
     * @param $text
     *
     * @return array|string
     */
    private static function parseSquareBracketsAttributes( $text )
    {
        $atts = [];
        $pattern = self::getSquareBracketsAttrRegex();
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);

        if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) )
        {
            foreach ($match as $m)
            {
                if (!empty($m[1]))
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                elseif (!empty($m[3]))
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                elseif (!empty($m[5]))
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                elseif (isset($m[7]) && strlen($m[7]))
                    $atts[] = stripcslashes($m[7]);
                elseif (isset($m[8]))
                    $atts[] = stripcslashes($m[8]);
            }

            foreach( $atts as &$value )
            {
                if ( false !== strpos( $value, '<' ) )
                {
                    if ( 1 !== preg_match( '/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value ) )
                    {
                        $value = '';
                    }
                }
            }
        }
        else
        {
            $atts = ltrim($text);
        }

        return $atts;
    }

    /**
     * @return string
     */
    private static function getSquareBracketsAttrRegex()
    {
        return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
    }

    /**
     * @return string
     */
    private static function getRegex()
    {
        $allowedRex = join( '|', self::$allowedTags );

        return '\\[(\\[?)(' . $allowedRex . ')(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*+(?:\\[(?!\\/\\2\\])[^\\[]*+)*+)\\[\\/\\2\\])?)(\\]?)';
    }

}