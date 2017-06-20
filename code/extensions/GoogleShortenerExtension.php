<?php


class GoogleShortenerExtension extends DataExtension
{

    protected static $social_media = [
        'TW',
        'FB'
    ];

    protected static $required_utm = [
        'UTMSource',
        'UTMMedium',
        'UTMCampaign',
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
        if ($this->owner->destroy) {
            $this->owner->ShortURL()->delete();
            $this->owner->ShortURLID = 0;
        }

        $utmData = SiteConfig::current_site_config(); // @todo

        $apiKey = GoogleShortenerService::config()->get('googlapi');
        $shortURL = ShortURL::get()->filter(['ID' => $this->owner->ShortURLID])->first();

        if ($apiKey && (!$shortURL || !$shortURL->exists())) {
            $shortURL = ShortURL::create();

            $longUrl = $this->owner->AbsoluteLink();

            $postData = array(
                'longUrl' => $longUrl,
            );

            $service = Injector::inst()->get('GoogleShortenerService');

            $result = $service->getShortLink($postData);

            // Always get a standard short URL
            if ($result && $result->id) {
                $shortURL->ShortURL = $result->id;
                $id = $shortURL->write();
                $this->owner->ShortURLID = $id;
            }
        }
        parent::onBeforeWrite();

    }

    public function updateCMSFields(FieldList $fields)
    {
        parent::updateCMSFields($fields);
        $fields->removeByName(['ShortURL']);
        $shortURL = ShortURL::get()->filter(['ID' => $this->owner->ShortURLID])->first();
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
                ]
            );
        }
    }

}

/*
 *
    Campaign Source (utm_source) – Required parameter to identify the source of your traffic such as: search engine, newsletter, or other referral.
    Campaign Medium (utm_medium) – Required parameter to identify the medium the link was used upon such as: email, CPC, or other method of sharing.
    Campaign Term (utm_term) – Optional parameter suggested for paid search to identify keywords for your ad. You can skip this for Google AdWords if you have connected your AdWords and Analytics accounts and use the auto-tagging feature instead.
    Campaign Content (utm_content) – Optional parameter for additional details for A/B testing and content-targeted ads.
    Campaign Name (utm_campaign) – Required parameter to identify a specific product promotion or strategic campaign such as a spring sale or other promotion.

 */