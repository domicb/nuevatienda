<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v8/services/campaign_service.proto

namespace GPBMetadata\Google\Ads\GoogleAds\V8\Services;

class CampaignService
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
        \GPBMetadata\Google\Protobuf\FieldMask::initOnce();
        \GPBMetadata\Google\Api\Client::initOnce();
        \GPBMetadata\Google\Protobuf\Any::initOnce();
        \GPBMetadata\Google\Rpc\Status::initOnce();
        $pool->internalAddGeneratedFile(
            '
�
5google/ads/googleads/v8/common/custom_parameter.protogoogle.ads.googleads.v8.common"I
CustomParameter
key (	H �
value (	H�B
_keyB
_valueB�
"com.google.ads.googleads.v8.commonBCustomParameterProtoPZDgoogle.golang.org/genproto/googleapis/ads/googleads/v8/common;common�GAA�Google.Ads.GoogleAds.V8.Common�Google\\Ads\\GoogleAds\\V8\\Common�"Google::Ads::GoogleAds::V8::Commonbproto3
�
Dgoogle/ads/googleads/v8/enums/target_impression_share_location.protogoogle.ads.googleads.v8.enums"�
!TargetImpressionShareLocationEnum"~
TargetImpressionShareLocation
UNSPECIFIED 
UNKNOWN
ANYWHERE_ON_PAGE
TOP_OF_PAGE
ABSOLUTE_TOP_OF_PAGEB�
!com.google.ads.googleads.v8.enumsB"TargetImpressionShareLocationProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
,google/ads/googleads/v8/common/bidding.protogoogle.ads.googleads.v8.commongoogle/api/annotations.proto"L

Commission#
commission_rate_micros (H �B
_commission_rate_micros"
EnhancedCpc"G
	ManualCpc!
enhanced_cpc_enabled (H �B
_enhanced_cpc_enabled"
	ManualCpm"
	ManualCpv")
MaximizeConversions

target_cpa (".
MaximizeConversionValue
target_roas ("�
	TargetCpa
target_cpa_micros (H �#
cpc_bid_ceiling_micros (H�!
cpc_bid_floor_micros (H�B
_target_cpa_microsB
_cpc_bid_ceiling_microsB
_cpc_bid_floor_micros"
	TargetCpm"�
TargetImpressionSharep
location (2^.google.ads.googleads.v8.enums.TargetImpressionShareLocationEnum.TargetImpressionShareLocation%
location_fraction_micros (H �#
cpc_bid_ceiling_micros (H�B
_location_fraction_microsB
_cpc_bid_ceiling_micros"�

TargetRoas
target_roas (H �#
cpc_bid_ceiling_micros (H�!
cpc_bid_floor_micros (H�B
_target_roasB
_cpc_bid_ceiling_microsB
_cpc_bid_floor_micros"�
TargetSpend$
target_spend_micros (BH �#
cpc_bid_ceiling_micros (H�B
_target_spend_microsB
_cpc_bid_ceiling_micros"�

PercentCpc#
cpc_bid_ceiling_micros (H �!
enhanced_cpc_enabled (H�B
_cpc_bid_ceiling_microsB
_enhanced_cpc_enabledB�
"com.google.ads.googleads.v8.commonBBiddingProtoPZDgoogle.golang.org/genproto/googleapis/ads/googleads/v8/common;common�GAA�Google.Ads.GoogleAds.V8.Common�Google\\Ads\\GoogleAds\\V8\\Common�"Google::Ads::GoogleAds::V8::Commonbproto3
�
<google/ads/googleads/v8/enums/frequency_cap_event_type.protogoogle.ads.googleads.v8.enums"r
FrequencyCapEventTypeEnum"U
FrequencyCapEventType
UNSPECIFIED 
UNKNOWN

IMPRESSION

VIDEO_VIEWB�
!com.google.ads.googleads.v8.enumsBFrequencyCapEventTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
@google/ads/googleads/v8/enums/advertising_channel_sub_type.protogoogle.ads.googleads.v8.enums"�
AdvertisingChannelSubTypeEnum"�
AdvertisingChannelSubType
UNSPECIFIED 
UNKNOWN
SEARCH_MOBILE_APP
DISPLAY_MOBILE_APP
SEARCH_EXPRESS
DISPLAY_EXPRESS
SHOPPING_SMART_ADS
DISPLAY_GMAIL_AD
DISPLAY_SMART_CAMPAIGN
VIDEO_OUTSTREAM	
VIDEO_ACTION

VIDEO_NON_SKIPPABLE
APP_CAMPAIGN
APP_CAMPAIGN_FOR_ENGAGEMENT
LOCAL_CAMPAIGN#
SHOPPING_COMPARISON_LISTING_ADS
SMART_CAMPAIGN
VIDEO_SEQUENCEB�
!com.google.ads.googleads.v8.enumsBAdvertisingChannelSubTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
<google/ads/googleads/v8/enums/advertising_channel_type.protogoogle.ads.googleads.v8.enums"�
AdvertisingChannelTypeEnum"�
AdvertisingChannelType
UNSPECIFIED 
UNKNOWN

SEARCH
DISPLAY
SHOPPING	
HOTEL	
VIDEO
MULTI_CHANNEL	
LOCAL	
SMART	B�
!com.google.ads.googleads.v8.enumsBAdvertisingChannelTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
7google/ads/googleads/v8/enums/frequency_cap_level.protogoogle.ads.googleads.v8.enums"w
FrequencyCapLevelEnum"^
FrequencyCapLevel
UNSPECIFIED 
UNKNOWN
AD_GROUP_AD
AD_GROUP
CAMPAIGNB�
!com.google.ads.googleads.v8.enumsBFrequencyCapLevelProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
;google/ads/googleads/v8/enums/frequency_cap_time_unit.protogoogle.ads.googleads.v8.enums"n
FrequencyCapTimeUnitEnum"R
FrequencyCapTimeUnit
UNSPECIFIED 
UNKNOWN
DAY
WEEK	
MONTHB�
!com.google.ads.googleads.v8.enumsBFrequencyCapTimeUnitProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
2google/ads/googleads/v8/common/frequency_cap.protogoogle.ads.googleads.v8.common7google/ads/googleads/v8/enums/frequency_cap_level.proto;google/ads/googleads/v8/enums/frequency_cap_time_unit.protogoogle/api/annotations.proto"k
FrequencyCapEntry<
key (2/.google.ads.googleads.v8.common.FrequencyCapKey
cap (H �B
_cap"�
FrequencyCapKeyU
level (2F.google.ads.googleads.v8.enums.FrequencyCapLevelEnum.FrequencyCapLevelb

event_type (2N.google.ads.googleads.v8.enums.FrequencyCapEventTypeEnum.FrequencyCapEventType_
	time_unit (2L.google.ads.googleads.v8.enums.FrequencyCapTimeUnitEnum.FrequencyCapTimeUnit
time_length (H �B
_time_lengthB�
"com.google.ads.googleads.v8.commonBFrequencyCapProtoPZDgoogle.golang.org/genproto/googleapis/ads/googleads/v8/common;common�GAA�Google.Ads.GoogleAds.V8.Common�Google\\Ads\\GoogleAds\\V8\\Common�"Google::Ads::GoogleAds::V8::Commonbproto3
�
>google/ads/googleads/v8/common/real_time_bidding_setting.protogoogle.ads.googleads.v8.common"8
RealTimeBiddingSetting
opt_in (H �B	
_opt_inB�
"com.google.ads.googleads.v8.commonBRealTimeBiddingSettingProtoPZDgoogle.golang.org/genproto/googleapis/ads/googleads/v8/common;common�GAA�Google.Ads.GoogleAds.V8.Common�Google\\Ads\\GoogleAds\\V8\\Common�"Google::Ads::GoogleAds::V8::Commonbproto3
�
7google/ads/googleads/v8/enums/targeting_dimension.protogoogle.ads.googleads.v8.enums"�
TargetingDimensionEnum"�
TargetingDimension
UNSPECIFIED 
UNKNOWN
KEYWORD
AUDIENCE	
TOPIC

GENDER
	AGE_RANGE
	PLACEMENT
PARENTAL_STATUS
INCOME_RANGE	B�
!com.google.ads.googleads.v8.enumsBTargetingDimensionProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
6google/ads/googleads/v8/common/targeting_setting.protogoogle.ads.googleads.v8.commongoogle/api/annotations.proto"�
TargetingSettingN
target_restrictions (21.google.ads.googleads.v8.common.TargetRestrictiona
target_restriction_operations (2:.google.ads.googleads.v8.common.TargetRestrictionOperation"�
TargetRestrictione
targeting_dimension (2H.google.ads.googleads.v8.enums.TargetingDimensionEnum.TargetingDimension
bid_only (H �B
	_bid_only"�
TargetRestrictionOperationU
operator (2C.google.ads.googleads.v8.common.TargetRestrictionOperation.Operator@
value (21.google.ads.googleads.v8.common.TargetRestriction"=
Operator
UNSPECIFIED 
UNKNOWN
ADD

REMOVEB�
"com.google.ads.googleads.v8.commonBTargetingSettingProtoPZDgoogle.golang.org/genproto/googleapis/ads/googleads/v8/common;common�GAA�Google.Ads.GoogleAds.V8.Common�Google\\Ads\\GoogleAds\\V8\\Common�"Google::Ads::GoogleAds::V8::Commonbproto3
�
Bgoogle/ads/googleads/v8/enums/ad_serving_optimization_status.protogoogle.ads.googleads.v8.enums"�
AdServingOptimizationStatusEnum"�
AdServingOptimizationStatus
UNSPECIFIED 
UNKNOWN
OPTIMIZE
CONVERSION_OPTIMIZE

ROTATE
ROTATE_INDEFINITELY
UNAVAILABLEB�
!com.google.ads.googleads.v8.enumsB AdServingOptimizationStatusProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
:google/ads/googleads/v8/enums/app_campaign_app_store.protogoogle.ads.googleads.v8.enums"y
AppCampaignAppStoreEnum"^
AppCampaignAppStore
UNSPECIFIED 
UNKNOWN
APPLE_APP_STORE
GOOGLE_APP_STOREB�
!com.google.ads.googleads.v8.enumsBAppCampaignAppStoreProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
Kgoogle/ads/googleads/v8/enums/app_campaign_bidding_strategy_goal_type.protogoogle.ads.googleads.v8.enums"�
&AppCampaignBiddingStrategyGoalTypeEnum"�
"AppCampaignBiddingStrategyGoalType
UNSPECIFIED 
UNKNOWN)
%OPTIMIZE_INSTALLS_TARGET_INSTALL_COST3
/OPTIMIZE_IN_APP_CONVERSIONS_TARGET_INSTALL_COST6
2OPTIMIZE_IN_APP_CONVERSIONS_TARGET_CONVERSION_COST(
$OPTIMIZE_RETURN_ON_ADVERTISING_SPENDB�
!com.google.ads.googleads.v8.enumsB\'AppCampaignBiddingStrategyGoalTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
4google/ads/googleads/v8/enums/asset_field_type.protogoogle.ads.googleads.v8.enums"�
AssetFieldTypeEnum"�
AssetFieldType
UNSPECIFIED 
UNKNOWN
HEADLINE
DESCRIPTION
MANDATORY_AD_TEXT
MARKETING_IMAGE
MEDIA_BUNDLE
YOUTUBE_VIDEO
BOOK_ON_GOOGLE
	LEAD_FORM	
	PROMOTION

CALLOUT
STRUCTURED_SNIPPET
SITELINKB�
!com.google.ads.googleads.v8.enumsBAssetFieldTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
<google/ads/googleads/v8/enums/brand_safety_suitability.protogoogle.ads.googleads.v8.enums"�
BrandSafetySuitabilityEnum"}
BrandSafetySuitability
UNSPECIFIED 
UNKNOWN
EXPANDED_INVENTORY
STANDARD_INVENTORY
LIMITED_INVENTORYB�
!com.google.ads.googleads.v8.enumsBBrandSafetySuitabilityProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
9google/ads/googleads/v8/enums/bidding_strategy_type.protogoogle.ads.googleads.v8.enums"�
BiddingStrategyTypeEnum"�
BiddingStrategyType
UNSPECIFIED 
UNKNOWN

COMMISSION
ENHANCED_CPC

MANUAL_CPC

MANUAL_CPM

MANUAL_CPV
MAXIMIZE_CONVERSIONS

MAXIMIZE_CONVERSION_VALUE
PAGE_ONE_PROMOTED
PERCENT_CPC

TARGET_CPA

TARGET_CPM
TARGET_IMPRESSION_SHARE
TARGET_OUTRANK_SHARE
TARGET_ROAS
TARGET_SPEND	B�
!com.google.ads.googleads.v8.enumsBBiddingStrategyTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
<google/ads/googleads/v8/enums/campaign_experiment_type.protogoogle.ads.googleads.v8.enums"y
CampaignExperimentTypeEnum"[
CampaignExperimentType
UNSPECIFIED 
UNKNOWN
BASE	
DRAFT

EXPERIMENTB�
!com.google.ads.googleads.v8.enumsBCampaignExperimentTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
;google/ads/googleads/v8/enums/campaign_serving_status.protogoogle.ads.googleads.v8.enums"�
CampaignServingStatusEnum"s
CampaignServingStatus
UNSPECIFIED 
UNKNOWN
SERVING
NONE	
ENDED
PENDING
	SUSPENDEDB�
!com.google.ads.googleads.v8.enumsBCampaignServingStatusProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
3google/ads/googleads/v8/enums/campaign_status.protogoogle.ads.googleads.v8.enums"j
CampaignStatusEnum"T
CampaignStatus
UNSPECIFIED 
UNKNOWN
ENABLED

PAUSED
REMOVEDB�
!com.google.ads.googleads.v8.enumsBCampaignStatusProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
8google/ads/googleads/v8/enums/location_source_type.protogoogle.ads.googleads.v8.enums"s
LocationSourceTypeEnum"Y
LocationSourceType
UNSPECIFIED 
UNKNOWN
GOOGLE_MY_BUSINESS
	AFFILIATEB�
!com.google.ads.googleads.v8.enumsBLocationSourceTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
<google/ads/googleads/v8/enums/negative_geo_target_type.protogoogle.ads.googleads.v8.enums"z
NegativeGeoTargetTypeEnum"]
NegativeGeoTargetType
UNSPECIFIED 
UNKNOWN
PRESENCE_OR_INTEREST
PRESENCEB�
!com.google.ads.googleads.v8.enumsBNegativeGeoTargetTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
:google/ads/googleads/v8/enums/optimization_goal_type.protogoogle.ads.googleads.v8.enums"y
OptimizationGoalTypeEnum"]
OptimizationGoalType
UNSPECIFIED 
UNKNOWN
CALL_CLICKS
DRIVING_DIRECTIONSB�
!com.google.ads.googleads.v8.enumsBOptimizationGoalTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
0google/ads/googleads/v8/enums/payment_mode.protogoogle.ads.googleads.v8.enums"�
PaymentModeEnum"n
PaymentMode
UNSPECIFIED 
UNKNOWN

CLICKS
CONVERSION_VALUE
CONVERSIONS

GUEST_STAYB�
!com.google.ads.googleads.v8.enumsBPaymentModeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
<google/ads/googleads/v8/enums/positive_geo_target_type.protogoogle.ads.googleads.v8.enums"�
PositiveGeoTargetTypeEnum"r
PositiveGeoTargetType
UNSPECIFIED 
UNKNOWN
PRESENCE_OR_INTEREST
SEARCH_INTEREST
PRESENCEB�
!com.google.ads.googleads.v8.enumsBPositiveGeoTargetTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
9google/ads/googleads/v8/enums/response_content_type.protogoogle.ads.googleads.v8.enums"o
ResponseContentTypeEnum"T
ResponseContentType
UNSPECIFIED 
RESOURCE_NAME_ONLY
MUTABLE_RESOURCEB�
!com.google.ads.googleads.v8.enumsBResponseContentTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
Bgoogle/ads/googleads/v8/enums/vanity_pharma_display_url_mode.protogoogle.ads.googleads.v8.enums"�
VanityPharmaDisplayUrlModeEnum"q
VanityPharmaDisplayUrlMode
UNSPECIFIED 
UNKNOWN
MANUFACTURER_WEBSITE_URL
WEBSITE_DESCRIPTIONB�
!com.google.ads.googleads.v8.enumsBVanityPharmaDisplayUrlModeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
6google/ads/googleads/v8/enums/vanity_pharma_text.protogoogle.ads.googleads.v8.enums"�
VanityPharmaTextEnum"�
VanityPharmaText
UNSPECIFIED 
UNKNOWN%
!PRESCRIPTION_TREATMENT_WEBSITE_EN%
!PRESCRIPTION_TREATMENT_WEBSITE_ES"
PRESCRIPTION_DEVICE_WEBSITE_EN"
PRESCRIPTION_DEVICE_WEBSITE_ES
MEDICAL_DEVICE_WEBSITE_EN
MEDICAL_DEVICE_WEBSITE_ES%
!PREVENTATIVE_TREATMENT_WEBSITE_EN%
!PREVENTATIVE_TREATMENT_WEBSITE_ES	)
%PRESCRIPTION_CONTRACEPTION_WEBSITE_EN
)
%PRESCRIPTION_CONTRACEPTION_WEBSITE_ES#
PRESCRIPTION_VACCINE_WEBSITE_EN#
PRESCRIPTION_VACCINE_WEBSITE_ESB�
!com.google.ads.googleads.v8.enumsBVanityPharmaTextProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�>
0google/ads/googleads/v8/resources/campaign.proto!google.ads.googleads.v8.resources5google/ads/googleads/v8/common/custom_parameter.proto2google/ads/googleads/v8/common/frequency_cap.proto>google/ads/googleads/v8/common/real_time_bidding_setting.proto6google/ads/googleads/v8/common/targeting_setting.protoBgoogle/ads/googleads/v8/enums/ad_serving_optimization_status.proto@google/ads/googleads/v8/enums/advertising_channel_sub_type.proto<google/ads/googleads/v8/enums/advertising_channel_type.proto:google/ads/googleads/v8/enums/app_campaign_app_store.protoKgoogle/ads/googleads/v8/enums/app_campaign_bidding_strategy_goal_type.proto4google/ads/googleads/v8/enums/asset_field_type.proto9google/ads/googleads/v8/enums/bidding_strategy_type.proto<google/ads/googleads/v8/enums/brand_safety_suitability.proto<google/ads/googleads/v8/enums/campaign_experiment_type.proto;google/ads/googleads/v8/enums/campaign_serving_status.proto3google/ads/googleads/v8/enums/campaign_status.proto8google/ads/googleads/v8/enums/location_source_type.proto<google/ads/googleads/v8/enums/negative_geo_target_type.proto:google/ads/googleads/v8/enums/optimization_goal_type.proto0google/ads/googleads/v8/enums/payment_mode.proto<google/ads/googleads/v8/enums/positive_geo_target_type.protoBgoogle/ads/googleads/v8/enums/vanity_pharma_display_url_mode.proto6google/ads/googleads/v8/enums/vanity_pharma_text.protogoogle/api/field_behavior.protogoogle/api/resource.protogoogle/api/annotations.proto"�1
Campaign@
resource_name (	B)�A�A#
!googleads.googleapis.com/Campaign
id; (B�AH�
name: (	H�P
status (2@.google.ads.googleads.v8.enums.CampaignStatusEnum.CampaignStatusk
serving_status (2N.google.ads.googleads.v8.enums.CampaignServingStatusEnum.CampaignServingStatusB�A�
ad_serving_optimization_status (2Z.google.ads.googleads.v8.enums.AdServingOptimizationStatusEnum.AdServingOptimizationStatusw
advertising_channel_type	 (2P.google.ads.googleads.v8.enums.AdvertisingChannelTypeEnum.AdvertisingChannelTypeB�A�
advertising_channel_sub_type
 (2V.google.ads.googleads.v8.enums.AdvertisingChannelSubTypeEnum.AdvertisingChannelSubTypeB�A"
tracking_url_template< (	H�N
url_custom_parameters (2/.google.ads.googleads.v8.common.CustomParameterY
real_time_bidding_setting\' (26.google.ads.googleads.v8.common.RealTimeBiddingSettingU
network_settings (2;.google.ads.googleads.v8.resources.Campaign.NetworkSettingsX
hotel_setting  (2<.google.ads.googleads.v8.resources.Campaign.HotelSettingInfoB�Ag
dynamic_search_ads_setting! (2C.google.ads.googleads.v8.resources.Campaign.DynamicSearchAdsSettingU
shopping_setting$ (2;.google.ads.googleads.v8.resources.Campaign.ShoppingSettingK
targeting_setting+ (20.google.ads.googleads.v8.common.TargetingSettinga
geo_target_type_setting/ (2@.google.ads.googleads.v8.resources.Campaign.GeoTargetTypeSetting`
local_campaign_setting2 (2@.google.ads.googleads.v8.resources.Campaign.LocalCampaignSetting\\
app_campaign_setting3 (2>.google.ads.googleads.v8.resources.Campaign.AppCampaignSetting>
labels= (	B.�A�A(
&googleads.googleapis.com/CampaignLabeln
experiment_type (2P.google.ads.googleads.v8.enums.CampaignExperimentTypeEnum.CampaignExperimentTypeB�AE
base_campaign8 (	B)�A�A#
!googleads.googleapis.com/CampaignH�J
campaign_budget> (	B,�A)
\'googleads.googleapis.com/CampaignBudgetH�n
bidding_strategy_type (2J.google.ads.googleads.v8.enums.BiddingStrategyTypeEnum.BiddingStrategyTypeB�A_
accessible_bidding_strategyG (	B:�A�A4
2googleads.googleapis.com/AccessibleBiddingStrategy

start_date? (	H�
end_date@ (	H�
final_url_suffixA (	H�I
frequency_caps( (21.google.ads.googleads.v8.common.FrequencyCapEntry}
video_brand_safety_suitability* (2P.google.ads.googleads.v8.enums.BrandSafetySuitabilityEnum.BrandSafetySuitabilityB�AO
vanity_pharma, (28.google.ads.googleads.v8.resources.Campaign.VanityPharmaa
selective_optimization- (2A.google.ads.googleads.v8.resources.Campaign.SelectiveOptimizationf
optimization_goal_setting6 (2C.google.ads.googleads.v8.resources.Campaign.OptimizationGoalSettingZ
tracking_setting. (2;.google.ads.googleads.v8.resources.Campaign.TrackingSettingB�AP
payment_mode4 (2:.google.ads.googleads.v8.enums.PaymentModeEnum.PaymentMode$
optimization_scoreB (B�AH	�k
!excluded_parent_asset_field_typesE (2@.google.ads.googleads.v8.enums.AssetFieldTypeEnum.AssetFieldTypeI
bidding_strategyC (	B-�A*
(googleads.googleapis.com/BiddingStrategyH @

commission1 (2*.google.ads.googleads.v8.common.CommissionH ?

manual_cpc (2).google.ads.googleads.v8.common.ManualCpcH ?

manual_cpm (2).google.ads.googleads.v8.common.ManualCpmH D

manual_cpv% (2).google.ads.googleads.v8.common.ManualCpvB�AH S
maximize_conversions (23.google.ads.googleads.v8.common.MaximizeConversionsH \\
maximize_conversion_value (27.google.ads.googleads.v8.common.MaximizeConversionValueH ?

target_cpa (2).google.ads.googleads.v8.common.TargetCpaH X
target_impression_share0 (25.google.ads.googleads.v8.common.TargetImpressionShareH A
target_roas (2*.google.ads.googleads.v8.common.TargetRoasH C
target_spend (2+.google.ads.googleads.v8.common.TargetSpendH A
percent_cpc" (2*.google.ads.googleads.v8.common.PercentCpcH ?

target_cpm) (2).google.ads.googleads.v8.common.TargetCpmH �
NetworkSettings!
target_google_search (H �"
target_search_network (H�#
target_content_network (H�*
target_partner_search_network (H�B
_target_google_searchB
_target_search_networkB
_target_content_networkB 
_target_partner_search_networkI
HotelSettingInfo!
hotel_center_id (B�AH �B
_hotel_center_idB
TrackingSetting
tracking_url (	B�AH �B
_tracking_url�
OptimizationGoalSettingm
optimization_goal_types (2L.google.ads.googleads.v8.enums.OptimizationGoalTypeEnum.OptimizationGoalType�
DynamicSearchAdsSetting
domain_name (	B�A
language_code (	B�A#
use_supplied_urls_only (H �1
feeds	 (	B"�A
googleads.googleapis.com/FeedB
_use_supplied_urls_only�
ShoppingSetting
merchant_id (B�AH �
sales_country (	B�AH�
campaign_priority (H�
enable_local (H�B
_merchant_idB
_sales_countryB
_campaign_priorityB
_enable_localc
SelectiveOptimizationJ
conversion_actions (	B.�A+
)googleads.googleapis.com/ConversionAction�
GeoTargetTypeSettingp
positive_geo_target_type (2N.google.ads.googleads.v8.enums.PositiveGeoTargetTypeEnum.PositiveGeoTargetTypep
negative_geo_target_type (2N.google.ads.googleads.v8.enums.NegativeGeoTargetTypeEnum.NegativeGeoTargetType~
LocalCampaignSettingf
location_source_type (2H.google.ads.googleads.v8.enums.LocationSourceTypeEnum.LocationSourceType�
AppCampaignSetting�
bidding_strategy_goal_type (2h.google.ads.googleads.v8.enums.AppCampaignBiddingStrategyGoalTypeEnum.AppCampaignBiddingStrategyGoalType
app_id (	B�AH �b
	app_store (2J.google.ads.googleads.v8.enums.AppCampaignAppStoreEnum.AppCampaignAppStoreB�AB	
_app_id�
VanityPharma�
vanity_pharma_display_url_mode (2X.google.ads.googleads.v8.enums.VanityPharmaDisplayUrlModeEnum.VanityPharmaDisplayUrlMode`
vanity_pharma_text (2D.google.ads.googleads.v8.enums.VanityPharmaTextEnum.VanityPharmaText:W�AT
!googleads.googleapis.com/Campaign/customers/{customer_id}/campaigns/{campaign_id}B
campaign_bidding_strategyB
_idB
_nameB
_tracking_url_templateB
_base_campaignB
_campaign_budgetB
_start_dateB
	_end_dateB
_final_url_suffixB
_optimization_scoreB�
%com.google.ads.googleads.v8.resourcesBCampaignProtoPZJgoogle.golang.org/genproto/googleapis/ads/googleads/v8/resources;resources�GAA�!Google.Ads.GoogleAds.V8.Resources�!Google\\Ads\\GoogleAds\\V8\\Resources�%Google::Ads::GoogleAds::V8::Resourcesbproto3
�
7google/ads/googleads/v8/services/campaign_service.proto google.ads.googleads.v8.services0google/ads/googleads/v8/resources/campaign.protogoogle/api/annotations.protogoogle/api/client.protogoogle/api/field_behavior.protogoogle/api/resource.proto google/protobuf/field_mask.protogoogle/rpc/status.proto"V
GetCampaignRequest@
resource_name (	B)�A�A#
!googleads.googleapis.com/Campaign"�
MutateCampaignsRequest
customer_id (	B�AL

operations (23.google.ads.googleads.v8.services.CampaignOperationB�A
partial_failure (
validate_only (i
response_content_type (2J.google.ads.googleads.v8.enums.ResponseContentTypeEnum.ResponseContentType"�
CampaignOperation/
update_mask (2.google.protobuf.FieldMask=
create (2+.google.ads.googleads.v8.resources.CampaignH =
update (2+.google.ads.googleads.v8.resources.CampaignH 
remove (	H B
	operation"�
MutateCampaignsResponse1
partial_failure_error (2.google.rpc.StatusG
results (26.google.ads.googleads.v8.services.MutateCampaignResult"l
MutateCampaignResult
resource_name (	=
campaign (2+.google.ads.googleads.v8.resources.Campaign2�
CampaignService�
GetCampaign4.google.ads.googleads.v8.services.GetCampaignRequest+.google.ads.googleads.v8.resources.Campaign"C���-+/v8/{resource_name=customers/*/campaigns/*}�Aresource_name�
MutateCampaigns8.google.ads.googleads.v8.services.MutateCampaignsRequest9.google.ads.googleads.v8.services.MutateCampaignsResponse"R���3"./v8/customers/{customer_id=*}/campaigns:mutate:*�Acustomer_id,operationsE�Agoogleads.googleapis.com�A\'https://www.googleapis.com/auth/adwordsB�
$com.google.ads.googleads.v8.servicesBCampaignServiceProtoPZHgoogle.golang.org/genproto/googleapis/ads/googleads/v8/services;services�GAA� Google.Ads.GoogleAds.V8.Services� Google\\Ads\\GoogleAds\\V8\\Services�$Google::Ads::GoogleAds::V8::Servicesbproto3'
        , true);
        static::$is_initialized = true;
    }
}

