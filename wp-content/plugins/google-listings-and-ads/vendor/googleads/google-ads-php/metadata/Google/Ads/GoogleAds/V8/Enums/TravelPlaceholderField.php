<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v8/enums/travel_placeholder_field.proto

namespace GPBMetadata\Google\Ads\GoogleAds\V8\Enums;

class TravelPlaceholderField
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
�
<google/ads/googleads/v8/enums/travel_placeholder_field.protogoogle.ads.googleads.v8.enums"�
TravelPlaceholderFieldEnum"�
TravelPlaceholderField
UNSPECIFIED 
UNKNOWN
DESTINATION_ID
	ORIGIN_ID	
TITLE
DESTINATION_NAME
ORIGIN_NAME	
PRICE
FORMATTED_PRICE

SALE_PRICE	
FORMATTED_SALE_PRICE

	IMAGE_URL
CATEGORY
CONTEXTUAL_KEYWORDS
DESTINATION_ADDRESS
	FINAL_URL
FINAL_MOBILE_URLS
TRACKING_URL
ANDROID_APP_LINK
SIMILAR_DESTINATION_IDS
IOS_APP_LINK
IOS_APP_STORE_IDB�
!com.google.ads.googleads.v8.enumsBTravelPlaceholderFieldProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3'
        , true);
        static::$is_initialized = true;
    }
}

