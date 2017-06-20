<?php


class ShortURL extends DataObject
{
    private static $db = [
        'ShortURL'   => 'Varchar(255)',
        'FBShortURL' => 'Varchar(255)',
        'TWShortURL' => 'Varchar(255)',
    ];

    private static $belongs_to = [
        'Page' => Page::class
    ];
}
