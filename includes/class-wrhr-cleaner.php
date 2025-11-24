<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WRHR_Cleaner
 *
 * Word / Mammoth kaynaklı HTML içeriklerini WRPR PDF Reader mantığıyla
 * güvenli biçimde temizler; içerik node'larını silmeden sarmalayıcıları
 * açar ve reader tarafına stabil, bloklanabilir HTML bırakır.
 */
class WRHR_Cleaner {

    /**
     * Clean raw HTML into a safe, flat structure.
     *
     * @param string $html Raw HTML string.
     * @return string Sanitized HTML ready for pagination.
     */
    public static function clean_html( $html ) {
        $raw_html = (string) $html;

        $raw_html = apply_filters( 'wrhr_clean_html_before_paginate', $raw_html );

        if ( '' === trim( $raw_html ) ) {
            return $raw_html;
        }

        $sanitizable_html = self::ensure_document_shell( $raw_html );

        $flags = defined( 'LIBXML_HTML_NOIMPLIED' )
            ? LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            : 0;

        $dom = new DOMDocument();
        libxml_use_internal_errors( true );
        $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $sanitizable_html, $flags );
        libxml_clear_errors();

        $xpath = new DOMXPath( $dom );

        self::remove_noise_nodes( $xpath );
        self::unwrap_tags( $xpath, array( 'span', 'font', 'section', 'article' ) );
        self::normalize_headings( $dom, $xpath );
        self::strip_attributes( $xpath );
        self::flatten_disallowed_tags( $xpath );
        self::remove_empty_blocks( $xpath );

        $body = $xpath->query( '//body' )->item( 0 );
        if ( ! $body ) {
            return $raw_html;
        }

        $clean = '';
        foreach ( iterator_to_array( $body->childNodes ) as $child ) {
            $clean .= $dom->saveHTML( $child );
        }

        $clean = trim( $clean );

        if ( '' === $clean ) {
            return $raw_html;
        }

        return $clean;
    }

    /**
     * Ensure the input can be parsed as a full HTML document.
     */
    private static function ensure_document_shell( $html ) {
        $sanitizable_html = $html;

        // Word çıktıları bazen <body> içerisine gömülü değil; güvenli bir kabuk ekle.
        if ( false === stripos( $sanitizable_html, '<html' ) || false === stripos( $sanitizable_html, '<body' ) ) {
            $sanitizable_html = '<html><body>' . $sanitizable_html . '</body></html>';
        }

        // <b> → <strong> ile temel semantik düzenleme.
        $sanitizable_html = str_replace( array( '<b', '</b>' ), array( '<strong', '</strong>' ), $sanitizable_html );

        return $sanitizable_html;
    }

    /**
     * Remove comments, script and style nodes.
     */
    private static function remove_noise_nodes( DOMXPath $xpath ) {
        foreach ( iterator_to_array( $xpath->query( '//comment() | //script | //style' ) ) as $node ) {
            if ( $node->parentNode ) {
                $node->parentNode->removeChild( $node );
            }
        }
    }

    /**
     * Unwrap given tag names while keeping children in place.
     */
    private static function unwrap_tags( DOMXPath $xpath, array $tags ) {
        $paths = array();

        foreach ( $tags as $tag ) {
            $paths[] = '//' . strtolower( $tag );
        }

        if ( empty( $paths ) ) {
            return;
        }

        $selector = implode( ' | ', $paths );
        $nodes    = iterator_to_array( $xpath->query( $selector ) );

        foreach ( $nodes as $node ) {
            while ( $node->firstChild ) {
                $node->parentNode->insertBefore( $node->firstChild, $node );
            }
            if ( $node->parentNode ) {
                $node->parentNode->removeChild( $node );
            }
        }
    }

    /**
     * Keep useful attributes while stripping inline noise.
     */
    private static function strip_attributes( DOMXPath $xpath ) {
        $allowed_global = array( 'colspan', 'rowspan', 'scope' );
        $nodes          = iterator_to_array( $xpath->query( '//*' ) );

        foreach ( $nodes as $node ) {
            if ( ! $node->hasAttributes() ) {
                continue;
            }

            $tag        = strtolower( $node->nodeName );
            $attributes = iterator_to_array( $node->attributes );

            foreach ( $attributes as $attr ) {
                $name = strtolower( $attr->name );

                if ( 'a' === $tag && 'href' === $name ) {
                    continue;
                }

                if ( in_array( $name, $allowed_global, true ) ) {
                    continue;
                }

                $node->removeAttribute( $attr->name );
            }
        }
    }

    /**
     * Normalize heading levels to avoid missing TOC items.
     */
    private static function normalize_headings( DOMDocument $dom, DOMXPath $xpath ) {
        // Word bazen H2 üretir; reader hiyerarşisini korumak için H3'e indir.
        $h2_nodes = iterator_to_array( $xpath->query( '//h2' ) );
        foreach ( $h2_nodes as $h2 ) {
            $h3 = $dom->createElement( 'h3' );
            while ( $h2->firstChild ) {
                $h3->appendChild( $h2->firstChild );
            }
            if ( $h2->parentNode ) {
                $h2->parentNode->replaceChild( $h3, $h2 );
            }
        }
    }

    /**
     * Flatten unsupported tags by unwrapping rather than deleting content.
     */
    private static function flatten_disallowed_tags( DOMXPath $xpath ) {
        $allowed = array(
            'html',
            'body',
            'div',
            'p',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'ul',
            'ol',
            'li',
            'strong',
            'em',
            'b',
            'i',
            'a',
            'table',
            'thead',
            'tbody',
            'tr',
            'td',
            'th',
            'br',
        );

        $nodes = iterator_to_array( $xpath->query( '//*' ) );

        foreach ( $nodes as $node ) {
            $name = strtolower( $node->nodeName );
            if ( in_array( $name, $allowed, true ) ) {
                continue;
            }

            while ( $node->firstChild ) {
                $node->parentNode->insertBefore( $node->firstChild, $node );
            }

            if ( $node->parentNode ) {
                $node->parentNode->removeChild( $node );
            }
        }
    }

    /**
     * Remove empty, presentation-only blocks without deleting real text nodes.
     */
    private static function remove_empty_blocks( DOMXPath $xpath ) {
        $targets = iterator_to_array( $xpath->query( '//p | //h1 | //h2 | //h3 | //h4 | //h5 | //h6 | //li | //div' ) );

        foreach ( $targets as $node ) {
            $text        = trim( $node->textContent );
            $has_element = false;

            foreach ( iterator_to_array( $node->childNodes ) as $child ) {
                if ( XML_ELEMENT_NODE === $child->nodeType ) {
                    $has_element = true;
                    break;
                }
            }

            // Boş, gereksiz wrapper'ları temizle; içerikli olanları bırak.
            if ( '' === $text && false === $has_element && $node->parentNode ) {
                $node->parentNode->removeChild( $node );
            }
        }
    }
}
