<?php

include( "passwords/passwords.php" );

define( "BASE_SQL", "SELECT hwaets.id as translationId, hwaets.hwaet as translation, hwaets.hwaet_eng as translationInEnglish, hwaets.hwaet_translit as translationTransliteration,
    hwaets.title, hwaets.title_translit as titleTransliteration, hwaets.year, hwaets.url, 
    hwaets.name_first as authorFirstName, hwaets.name_middle as authorMiddleName, hwaets.name_last as authorLastName, hwaets.name_additional as authorAdditionalName, hwaets.name_translit as authorTransliteration, hwaets.skeet_count,
    language.name AS language, language.name_eng as languageInEnglish, language.emoji AS languageEmoji, language.code, language.lastname_first, language.transliterate,
    format.name as format,
    country.emoji as countryEmoji, country.name AS countryName, country.name_eng as countryInEnglish
    FROM hwaets
    LEFT JOIN language on hwaets.language_id = language.id
    LEFT JOIN format on hwaets.format_id = format.id
    LEFT JOIN country on hwaets.country_id = country.id
    WHERE (hwaets.hwaet IS NOT NULL and hwaets.hwaet != '')" );
?>