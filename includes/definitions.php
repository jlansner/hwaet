<?php

include( "passswords/passwords.php" );

define( "RECENTLY_USED_COUNT", 10 );
define( "BASE_SQL", "SELECT translations.id as translationId, translations.translation, translations.translation_eng, not_translated,
    book.title, book.year, book.url, 
    author.first_name, author.middle_name, author.last_name, author.additional_name, 
    language.name AS language, language.name_eng as languageInEnglish, language.emoji AS languageEmoji, language.code,
    format.name as format,
    country.emoji as countryEmoji, country.name AS countryName, country.name_eng as countryInEnglish
    FROM translations
    LEFT JOIN book ON translations.book_id = book.id
    LEFT JOIN author on book.author_id = author.id
    LEFT JOIN language on book.language_id = language.id
    LEFT JOIN format on book.format_id = format.id
    LEFT JOIN country on book.country_id = country.id" );

?>