<?php

class ShortenerSiteConfigExtension extends DataExtension
{

    private static $db = [
        'FBUTMSource'   => 'Varchar(255)',
        'FBUTMMedium'   => 'Varchar(255)',
        'FBUTMCampaign' => 'Varchar(255)',
        'FBUTMTerm'     => 'Varchar(255)',
        'FBUTMContent'  => 'Varchar(255)',
        'TWUTMSource'   => 'Varchar(255)',
        'TWUTMMedium'   => 'Varchar(255)',
        'TWUTMCampaign' => 'Varchar(255)',
        'TWUTMTerm'     => 'Varchar(255)',
        'TWUTMContent'  => 'Varchar(255)',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        parent::updateCMSFields($fields);
        $fields->addFieldsToTab(
            'Root.ShortenerService',
            [
                HeaderField::create('FBUTM', 'Facebook UTM parameters'),
                $FBUTMSource = TextField::create('FBUTMSource', 'UTM Source for Facebook'),
                $FBUTMMedium = TextField::create('FBUTMMedium', 'UTM Medium for Facebook'),
                $FBUTMCampaign = TextField::create('FBUTMCampaign', 'UTM Campaign for Facebook'),
                $FBUTMTerm = TextField::create('FBUTMTerm', 'UTM Term for Facebook'),
                $FBUTMContent = TextField::create('FBUTMContent', 'UTM Content for facebook'),
                HeaderField::create('TWUTM', 'Twitter UTM parameters'),
                $TWUTMSource = TextField::create('TWUTMSource', 'UTM Source for Twitter'),
                $TWUTMMedium = TextField::create('TWUTMMedium', 'UTM Medium for Twitter'),
                $TWUTMCampaign = TextField::create('TWUTMCampaign', 'UTM Campaign for Twitter'),
                $TWUTMTerm = TextField::create('TWUTMTerm', 'UTM Term for Twitter'),
                $TWUTMContent = TextField::create('TWUTMContent', 'UTM Content for Twitter'),
            ]
        );
        $FBUTMSource->setDescription('Required parameter to identify the source of your traffic such as: search engine, newsletter, or other referral.');
        $FBUTMMedium->setDescription('Required parameter to identify the medium the link was used upon such as: email, CPC, or other method of sharing.');
        $FBUTMCampaign->setDescription('Required parameter to identify a specific product promotion or strategic campaign such as a spring sale or other promotion.');
        $FBUTMTerm->setDescription('Optional parameter suggested for paid search to identify keywords for your ad. You can skip this for Google AdWords if you have connected your AdWords and Analytics accounts and use the auto-tagging feature instead.');
        $FBUTMContent->setDescription('Optional parameter for additional details for A/B testing and content-targeted ads.');

        $TWUTMSource->setDescription('Required parameter to identify the source of your traffic such as: search engine, newsletter, or other referral.');
        $TWUTMMedium->setDescription('Required parameter to identify the medium the link was used upon such as: email, CPC, or other method of sharing.');
        $TWUTMCampaign->setDescription('Required parameter to identify a specific product promotion or strategic campaign such as a spring sale or other promotion.');
        $TWUTMTerm->setDescription('Optional parameter suggested for paid search to identify keywords for your ad. You can skip this for Google AdWords if you have connected your AdWords and Analytics accounts and use the auto-tagging feature instead.');
        $TWUTMContent->setDescription('Optional parameter for additional details for A/B testing and content-targeted ads.');
    }
}
