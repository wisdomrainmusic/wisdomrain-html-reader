<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WRHR_Cleaner
 *
 * Word / Mammoth kaynaklı HTML içerikleri A5/A6 reader için
 * temizleyen DOM tabanlı motor. WRPR PDF Reader'daki temizleyicinin
 * sadeleştirilmiş ve WRHR'e uyarlanmış sürümü.
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

        if ( '' === trim( $raw_html ) ) {
            return $raw_html;
        }

        $sanitizable_html = $raw_html;

        // Eğer full HTML yapısı yoksa body ile sar.
        if ( false === stripos( $sanitizable_html, '<html' ) || false === stripos( $sanitizable_html, '<body' ) ) {
            $sanitizable_html = '<html><body>' . $sanitizable_html . '</body></html>';
        }

        // <b> → <strong>
        $sanitizable_html = str_replace( array( '<b', '</b>' ), array( '<strong', '</strong>' ), $sanitizable_html );

        $flags = defined( 'LIBXML_HTML_NOIMPLIED' )
            ? LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            : 0;

        $dom = new DOMDocument();
        libxml_use_internal_errors( true );
        $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $sanitizable_html, $flags );
        libxml_clear_errors();

        $xpath = new DOMXPath( $dom );

        // Yorum, script ve style tag'larını kaldır.
        foreach ( iterator_to_array( $xpath->query( '//comment() | //script | //style' ) ) as $node ) {
            $node->parentNode->removeChild( $node );
        }

        self::unwrap_spans( $xpath );
        self::strip_attributes( $xpath );
        self::normalize_headings( $dom, $xpath );
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
     * <span> etiketlerini içindeki text'i koruyarak kaldır.
     */
    private static function unwrap_spans( DOMXPath $xpath ) {
        $spans = iterator_to_array( $xpath->query( '//span' ) );
        foreach ( $spans as $span ) {
            while ( $span->firstChild ) {
                $span->parentNode->insertBefore( $span->firstChild, $span );
            }
            if ( $span->parentNode ) {
                $span->parentNode->removeChild( $span );
            }
        }
    }

    /**
     * Tüm inline attribute'ları temizle.
     * Sadece <a href="..."> attribute'unu koruyoruz.
     */
    private static function strip_attributes( DOMXPath $xpath ) {
        $nodes = iterator_to_array( $xpath->query( '//*' ) );

        foreach ( $nodes as $node ) {
            if ( ! $node->hasAttributes() ) {
                continue;
            }

            $to_remove = array();

            foreach ( iterator_to_array( $node->attributes ) as $attr ) {
                $name  = strtolower( $attr->name );
                $tag   = strtolower( $node->nodeName );

                // Sadece link href'ini koru.
                if ( 'a' === $tag && 'href' === $name ) {
                    continue;
                }

                $to_remove[] = $attr->name;
            }

            foreach ( $to_remove as $name ) {
                $node->removeAttribute( $name );
            }
        }
    }

    /**
     * H2'leri H3'e normalize et (reader hiyerarşisi için).
     */
    private static function normalize_headings( DOMDocument $dom, DOMXPath $xpath ) {
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
     * İzin verilmeyen tüm tag'ları düzleştir.
     * Çocuklarını bir üst seviyeye taşı, kendi node'u sil.
     */
    private static function flatten_disallowed_tags( DOMXPath $xpath ) {
        $allowed = array( 'body', 'h1', 'h3', 'h4', 'p', 'ul', 'ol', 'li', 'strong', 'em', 'b', 'i', 'a' );
        $nodes   = iterator_to_array( $xpath->query( '//*' ) );

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
     * Boş blokları (p, h1, h3, h4, li) temizle.
     */
    private static function remove_empty_blocks( DOMXPath $xpath ) {
        $blocks = iterator_to_array( $xpath->query( '//p | //h1 | //h3 | //h4 | //li' ) );

        foreach ( $blocks as $node ) {
            $text        = trim( $node->textContent );
            $has_element = false;

            foreach ( iterator_to_array( $node->childNodes ) as $child ) {
                if ( XML_ELEMENT_NODE === $child->nodeType ) {
                    $has_element = true;
                    break;
                }
            }

            if ( '' === $text && false === $has_element && $node->parentNode ) {
                $node->parentNode->removeChild( $node );
            }
        }
    }
}
