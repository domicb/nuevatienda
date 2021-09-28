<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v8/services/ad_group_audience_view_service.proto

namespace GPBMetadata\Google\Ads\GoogleAds\V8\Services;

class AdGroupAudienceViewService
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
        \GPBMetadata\Google\Api\Client::initOnce();
        $pool->internalAddGeneratedFile(
            '
�
>google/ads/googleads/v8/resources/ad_group_audience_view.proto!google.ads.googleads.v8.resourcesgoogle/api/resource.protogoogle/api/annotations.proto"�
AdGroupAudienceViewK
resource_name (	B4�A�A.
,googleads.googleapis.com/AdGroupAudienceView:|�Ay
,googleads.googleapis.com/AdGroupAudienceViewIcustomers/{customer_id}/adGroupAudienceViews/{ad_group_id}~{criterion_id}B�
%com.google.ads.googleads.v8.resourcesBAdGroupAudienceViewProtoPZJgoogle.golang.org/genproto/googleapis/ads/googleads/v8/resources;resources�GAA�!Google.Ads.GoogleAds.V8.Resources�!Google\\Ads\\GoogleAds\\V8\\Resources�%Google::Ads::GoogleAds::V8::Resourcesbproto3
�
Egoogle/ads/googleads/v8/services/ad_group_audience_view_service.proto google.ads.googleads.v8.servicesgoogle/api/annotations.protogoogle/api/client.protogoogle/api/field_behavior.protogoogle/api/resource.proto"l
GetAdGroupAudienceViewRequestK
resource_name (	B4�A�A.
,googleads.googleapis.com/AdGroupAudienceView2�
AdGroupAudienceViewService�
GetAdGroupAudienceView?.google.ads.googleads.v8.services.GetAdGroupAudienceViewRequest6.google.ads.googleads.v8.resources.AdGroupAudienceView"N���86/v8/{resource_name=customers/*/adGroupAudienceViews/*}�Aresource_nameE�Agoogleads.googleapis.com�A\'https://www.googleapis.com/auth/adwordsB�
$com.google.ads.googleads.v8.servicesBAdGroupAudienceViewServiceProtoPZHgoogle.golang.org/genproto/googleapis/ads/googleads/v8/services;services�GAA� Google.Ads.GoogleAds.V8.Services� Google\\Ads\\GoogleAds\\V8\\Services�$Google::Ads::GoogleAds::V8::Servicesbproto3'
        , true);
        static::$is_initialized = true;
    }
}

