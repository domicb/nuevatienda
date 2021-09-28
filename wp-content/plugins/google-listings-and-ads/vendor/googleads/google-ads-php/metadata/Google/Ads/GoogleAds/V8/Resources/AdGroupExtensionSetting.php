<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v8/resources/ad_group_extension_setting.proto

namespace GPBMetadata\Google\Ads\GoogleAds\V8\Resources;

class AdGroupExtensionSetting
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();
        if (static::$is_initialized == true) {
          return;
        }
        \GPBMetadata\Google\Api\Http::initOnce();
        \GPBMetadata\Google\Api\Annotations::initOnce();
        \GPBMetadata\Google\Api\FieldBehavior::initOnce();
        \GPBMetadata\Google\Api\Resource::initOnce();
        $pool->internalAddGeneratedFile(
            '
�
<google/ads/googleads/v8/enums/extension_setting_device.protogoogle.ads.googleads.v8.enums"m
ExtensionSettingDeviceEnum"O
ExtensionSettingDevice
UNSPECIFIED 
UNKNOWN

MOBILE
DESKTOPB�
!com.google.ads.googleads.v8.enumsBExtensionSettingDeviceProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
2google/ads/googleads/v8/enums/extension_type.protogoogle.ads.googleads.v8.enums"�
ExtensionTypeEnum"�
ExtensionType
UNSPECIFIED 
UNKNOWN
NONE
APP
CALL
CALLOUT
MESSAGE	
PRICE
	PROMOTION
SITELINK

STRUCTURED_SNIPPET
LOCATION
AFFILIATE_LOCATION
HOTEL_CALLOUT	
IMAGEB�
!com.google.ads.googleads.v8.enumsBExtensionTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
Bgoogle/ads/googleads/v8/resources/ad_group_extension_setting.proto!google.ads.googleads.v8.resources2google/ads/googleads/v8/enums/extension_type.protogoogle/api/field_behavior.protogoogle/api/resource.protogoogle/api/annotations.proto"�
AdGroupExtensionSettingO
resource_name (	B8�A�A2
0googleads.googleapis.com/AdGroupExtensionSetting[
extension_type (2>.google.ads.googleads.v8.enums.ExtensionTypeEnum.ExtensionTypeB�A?
ad_group (	B(�A�A"
 googleads.googleapis.com/AdGroupH �M
extension_feed_items (	B/�A,
*googleads.googleapis.com/ExtensionFeedItem`
device (2P.google.ads.googleads.v8.enums.ExtensionSettingDeviceEnum.ExtensionSettingDevice:��A�
0googleads.googleapis.com/AdGroupExtensionSettingOcustomers/{customer_id}/adGroupExtensionSettings/{ad_group_id}~{extension_type}B
	_ad_groupB�
%com.google.ads.googleads.v8.resourcesBAdGroupExtensionSettingProtoPZJgoogle.golang.org/genproto/googleapis/ads/googleads/v8/resources;resources�GAA�!Google.Ads.GoogleAds.V8.Resources�!Google\\Ads\\GoogleAds\\V8\\Resources�%Google::Ads::GoogleAds::V8::Resourcesbproto3'
        , true);
        static::$is_initialized = true;
    }
}

