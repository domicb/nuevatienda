<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v8/enums/ad_destination_type.proto

namespace GPBMetadata\Google\Ads\GoogleAds\V8\Enums;

class AdDestinationType
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();
        if (static::$is_initialized == true) {
          return;
        }
        \GPBMetadata\Google\Api\Http::initOnce();
        \GPBMetadata\Google\Api\Annotations::initOnce();
        $pool->internalAddGeneratedFile(
            '
�
7google/ads/googleads/v8/enums/ad_destination_type.protogoogle.ads.googleads.v8.enums"�
AdDestinationTypeEnum"�
AdDestinationType
UNSPECIFIED 
UNKNOWN
NOT_APPLICABLE
WEBSITE
APP_DEEP_LINK
	APP_STORE

PHONE_CALL
MAP_DIRECTIONS
LOCATION_LISTING
MESSAGE	
	LEAD_FORM

YOUTUBE
UNMODELED_FOR_CONVERSIONSB�
!com.google.ads.googleads.v8.enumsBAdDestinationTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3'
        , true);
        static::$is_initialized = true;
    }
}

