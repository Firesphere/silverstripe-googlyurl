<?php


class GoogleShortenerExtension extends DataExtension
{

    /**
     * @var GoogleShortenerService
     */
    protected $service;

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

    protected static $utm_to_url = [
        'UTMSource'   => 'source',
        'UTMMedium'   => 'medium',
        'UTMCampaign' => 'campaign',
        'UTMContent'  => 'content',
        'UTMTerm'     => 'term'
    ];

    private static $has_one = [
        'ShortURL' => ShortURL::class
    ];

    /**
     * Create a short-url for this page
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if ($this->owner->destroy) {
            $this->owner->ShortURL()->delete();
            $this->owner->ShortURLID = 0;
        }

        $apiKey = GoogleShortenerService::config()->get('googlapi');
        $shortURL = ShortURL::get()->filter(['ID' => $this->owner->ShortURLID])->first();

        if ($apiKey && (!$shortURL || !$shortURL->exists())) {
            $shortURL = ShortURL::create();
            $id = $shortURL->write();
            $this->owner->ShortURLID = $id;

            $longUrl = $this->owner->AbsoluteLink();

            $postData = array(
                'longUrl' => $longUrl,
            );

            $this->service = Injector::inst()->get('GoogleShortenerService');

            $result = $this->service->getShortLink($postData);

            // Always get a standard short URL
            if ($result && $result->id) {
                $shortURL->ShortURL = $result->id;
            }
            $this->getUTMLinks($shortURL);

            $shortURL->write();
        }

    }

    public function getUTMLinks($shortURL)
    {
        $owner = $this->owner;
        $mediumString = [];
        foreach (['FB', 'TW'] as $medium) {
            if ($owner->$medium . 'UTMSource' && $owner->$medium . 'UTMMedium' && $owner->$medium . 'UTMCampaign') {
                foreach (static::$utm_to_url as $key => $value) {
                    $field = $medium . $key;
                    $mediumString[] = $value . '=' . $owner->{$field};
                }

                $result = $this->service->getShortLink([
                    'longUrl' => $owner->AbsoluteLink() . '?' . implode('&', $mediumString)
                ]);
                if ($result && $result->id) {
                    $shortURL->{$medium . 'ShortURL'} = $result->id;
                    $shortURL->write();
                }
            }
        }
    }

    public function updateCMSFields(FieldList $fields)
    {
        parent::updateCMSFields($fields);
        $fields->removeByName(['ShortURL']);
        $shortURL = ShortURL::get()->filter(['ID' => $this->owner->ShortURLID])->first();
        $fields->addFieldsToTab(
            'Root.ShareLinks',
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
        if ($shortURL && $shortURL->exists()) {
            $fields->addFieldsToTab(
                'Root.ShareLinks',
                [
                    LiteralField::create(
                        'ShortLink',
                        '<p><a target="_blank" href="' . $this->owner->ShortURL()->ShortURL . '">Short URL</a><br />'
                    ),
                    LiteralField::create(
                        'FBShortLink',
                        '<p><a target="_blank" href="' . $this->owner->ShortURL()->FBShortURL . '">Facebook share Short URL</a><br />'
                    ),
                    LiteralField::create(
                        'TWShortLink',
                        '<p><a target="_blank" href="' . $this->owner->ShortURL()->TWShortURL . '">Twitter share Short URL</a><br />'
                    ),
                    LiteralField::create(
                        'Statistics',
                        '<p>Copy the URL and add ".info" at the end to see the usage statistics'
                    ),
                    CheckboxField::create('destroy', 'Reset the short URL\'s')
                ],
                'FBUTM'
            );
        }

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
