<?php

/**
 * Détection de contenus injurieux / haineux pour modération automatique.
 * Normalise accents, séparateurs, leet, puis tolère ~1–2 erreurs (lettre changée,
 * manquante ou en trop) via distance de Levenshtein sur les tokens et sur les
 * segments « collés » (sans espace).
 */
class CommentModeration
{
    /** Mots courts (≤3) : distance max 1 uniquement, jamais détection « glissante » dans une longue chaîne */
    private const SHORT_FORBIDDEN_MAX_LEN = 3;

    /** Mots et insultes (référence, après normalisation ASCII a–z) */
    private const WORDS = [
        'con',
        'idiot', 'idiote', 'idiots', 'idiotes',
        'merde',
        'putain',
        'bordel',
        'connard', 'connasse', 'connards', 'connasses',
        'salaud', 'salauds', 'salope', 'salopes',
        'encule', 'enculer', 'enculee', 'enculees', 'encules',
        'batard', 'batards', 'batarde', 'batardes',
        'foutre',
        'chier',
        'negre', 'negres', 'negro', 'negros',
        'bougnoul', 'bougnoule', 'bougnoules',
        'bicot', 'bicots',
        'bamboula', 'bamboulas',
        'youpin', 'youpins', 'youtre', 'youtres',
        'crouille', 'crouilles',
        'raton', 'ratons',
        'guenon', 'guenons',
        'buter', 'bute', 'butee',
        'defoncer', 'defonce', 'defoncee',
        'creve', 'creves',
        'suicide', 'suicides',
    ];

    /** Phrases avec espaces (référence) */
    private const PHRASES_SPACED = [
        'trou du cul',
        'sale arabe',
        'sale juif',
        'sale blanc',
        'je vais te tuer',
        'creve sale chien',
        'suicide toi',
        'suicides toi',
    ];

    /** Formes collées (sans espace), pour fuzzy + exact */
    private const PHRASES_COLLAPSED = [
        'trouducul',
        'salearabe',
        'salejuif',
        'saleblanc',
        'jevaistetuer',
        'crevesalechien',
        'suicidetoi',
        'suicidestoi',
    ];

    public static function isToxic(string $text): bool
    {
        $t = trim($text);
        if ($t === '') {
            return false;
        }

        $norm = self::normalize($t);
        $spaced = $norm['spaced'];
        $collapsed = $norm['collapsed'];

        foreach (self::PHRASES_COLLAPSED as $p) {
            if ($p === '') {
                continue;
            }
            if (strpos($collapsed, $p) !== false) {
                return true;
            }
            if (self::fuzzyInCollapsed($collapsed, $p, self::phraseMaxDist())) {
                return true;
            }
        }

        $haystack = ' ' . $spaced . ' ';
        foreach (self::PHRASES_SPACED as $p) {
            if (strpos($haystack, ' ' . $p . ' ') !== false) {
                return true;
            }
        }

        $tokens = $spaced === '' ? [] : explode(' ', $spaced);
        foreach (self::PHRASES_SPACED as $p) {
            if (self::fuzzyPhraseInTokens($tokens, $p)) {
                return true;
            }
        }

        foreach (self::WORDS as $w) {
            if (preg_match('/\b' . preg_quote($w, '/') . '\b/ui', $spaced)) {
                return true;
            }
        }

        foreach ($tokens as $tok) {
            if ($tok === '') {
                continue;
            }
            foreach (self::WORDS as $w) {
                if (self::fuzzyTokenMatch($tok, $w)) {
                    return true;
                }
            }
        }

        foreach (self::WORDS as $w) {
            $lenF = strlen($w);
            if ($lenF < 5) {
                continue;
            }
            if (self::fuzzyInCollapsed($collapsed, $w, self::maxDistForWord($lenF))) {
                return true;
            }
        }

        return false;
    }

    private static function maxDistForWord(int $forbiddenLen): int
    {
        if ($forbiddenLen <= self::SHORT_FORBIDDEN_MAX_LEN) {
            return 1;
        }

        return 2;
    }

    /** Tolérance max (substitutions / lettres manquantes ou en trop) sur les phrases. */
    private static function phraseMaxDist(): int
    {
        return 2;
    }

    /**
     * Mot isolé (token) vs forme interdite : tolère substitutions / manques / lettres en trop.
     */
    private static function fuzzyTokenMatch(string $token, string $forbidden): bool
    {
        if ($forbidden === '') {
            return false;
        }

        $Lt = strlen($token);
        $Lf = strlen($forbidden);
        $maxDist = self::maxDistForWord($Lf);

        if (abs($Lt - $Lf) > $maxDist) {
            return false;
        }

        if ($Lt > 255 || $Lf > 255) {
            return false;
        }

        if ($Lt === $Lf && $Lf === 5) {
            $maxDist = min($maxDist, 1);
        }

        return levenshtein($token, $forbidden) <= $maxDist;
    }

    /**
     * Fenêtre glissante sur la chaîne collée (détecte insulte collée ou légèrement altérée).
     * Réservé aux aiguilles de longueur ≥ 5 pour éviter « con » dans « inconnu ».
     */
    private static function fuzzyInCollapsed(string $collapsed, string $needle, int $maxDist): bool
    {
        $Ln = strlen($needle);
        if ($Ln < 4) {
            return false;
        }

        $h = strlen($collapsed);
        if ($h === 0) {
            return false;
        }

        $minWin = max(1, $Ln - $maxDist);
        $maxWin = min($h, $Ln + $maxDist);

        for ($len = $minWin; $len <= $maxWin; $len++) {
            $effectiveMax = $maxDist;
            if ($len === $Ln && $Ln === 5) {
                $effectiveMax = min($effectiveMax, 1);
            }
            for ($i = 0; $i + $len <= $h; $i++) {
                $sub = substr($collapsed, $i, $len);
                if ($len > 255 || $Ln > 255) {
                    continue;
                }
                if (levenshtein($sub, $needle) <= $effectiveMax) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Phrase multi-mots : fenêtre de N tokens consécutifs vs phrase de référence.
     */
    private static function fuzzyPhraseInTokens(array $tokens, string $phraseSpaced): bool
    {
        $refWords = preg_split('/\s+/u', trim($phraseSpaced));
        $refWords = array_values(array_filter($refWords, static function ($x) {
            return $x !== '';
        }));
        $k = count($refWords);
        if ($k === 0) {
            return false;
        }

        $n = count($tokens);
        if ($n < $k) {
            return false;
        }

        $refJoined = implode(' ', $refWords);
        $maxDist = self::phraseMaxDist();

        for ($i = 0; $i <= $n - $k; $i++) {
            $chunk = implode(' ', array_slice($tokens, $i, $k));
            if (strlen($chunk) > 255 || strlen($refJoined) > 255) {
                continue;
            }
            if (levenshtein($chunk, $refJoined) <= $maxDist) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{spaced: string, collapsed: string}
     */
    private static function normalize(string $text): array
    {
        $s = mb_strtolower($text, 'UTF-8');
        $s = self::stripAccents($s);
        $s = self::applyLeetSubstitutions($s);
        $spaced = preg_replace('/[^a-z0-9]+/u', ' ', $s);
        $spaced = trim(preg_replace('/\s+/u', ' ', $spaced ?? ''));
        $collapsed = str_replace(' ', '', $spaced);

        return ['spaced' => $spaced, 'collapsed' => $collapsed];
    }

    private static function stripAccents(string $s): string
    {
        if (class_exists('Normalizer')) {
            $d = Normalizer::normalize($s, Normalizer::FORM_D);
            if ($d !== false) {
                $s = preg_replace('/\p{Mn}/u', '', $d);
                $c = Normalizer::normalize($s, Normalizer::FORM_C);
                if ($c !== false) {
                    $s = $c;
                }
            }
        } else {
            $conv = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            if ($conv !== false) {
                $s = $conv;
            }
        }

        return $s;
    }

    private static function applyLeetSubstitutions(string $s): string
    {
        $map = [
            '0' => 'o',
            '1' => 'i',
            '3' => 'e',
            '4' => 'a',
            '5' => 's',
            '7' => 't',
            '8' => 'b',
            '@' => 'a',
            '$' => 's',
            '!' => 'i',
            '|' => 'l',
        ];

        return strtr($s, $map);
    }
}
