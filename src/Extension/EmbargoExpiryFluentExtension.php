<?php

namespace Terraformers\EmbargoExpiry\Extension;

use Exception;
use SilverStripe\ORM\DataExtension;
use SuperClosure\SerializableClosure;
use TractorCow\Fluent\State\FluentState;

/**
 * Please be very aware that Fluent is not an included dependency for this module. As such, test coverage provided in
 * this module can't cov.
 *
 * Class EmbargoExpiryFluentExtension
 *
 * @package Terraformers\EmbargoExpiry\Extension
 */
class EmbargoExpiryFluentExtension extends DataExtension
{
    /**
     * Fluent specific configuration
     *
     * @var array|string[]
     */
    private static $field_include = [
        'DesiredPublishDate',
        'DesiredUnPublishDate',
        'PublishOnDate',
        'UnPublishOnDate',
        'PublishJobID',
        'UnPublishJobID',
    ];

    /**
     * @codeCoverageIgnore
     * @param array|string[] $options
     * @throws Exception
     */
    public function setLocaleOptions(array &$options): void
    {
        if (!class_exists(FluentState::class)) {
            throw new Exception('Fluent extension not available. Please add it to your compose requirements');
        }

        $locale = FluentState::singleton()->getLocale();

        // There's nothing to be done here if there is no active Locale.
        if (!$locale) {
            return;
        }

        // Locale isn't currently used in our Job, but if you subclass, you might find it useful for something.
        $options['locale'] = $locale;

        // Before we fetch our DataObject in the Job, we must have the request Locale set to our FluentState. Otherwise
        // you'll end up pulling the *base* record (EG: from SiteTree instead of SiteTree_Localised), and you'll also
        // publish/un-publish the *base* record.
        $options['onBeforeGetObject'] = new SerializableClosure(function () use ($locale): void {
            FluentState::singleton()->setLocale($locale);
        });
    }

    /**
     * @codeCoverageIgnore
     * @param array|string[] $options
     * @throws Exception
     */
    public function updatePublishTargetJobOptions(array &$options): void
    {
        $this->setLocaleOptions($options);
    }

    /**
     * @codeCoverageIgnore
     * @param array|string[] $options
     * @throws Exception
     */
    public function updateUnPublishTargetJobOptions(array &$options): void
    {
        $this->setLocaleOptions($options);
    }
}
