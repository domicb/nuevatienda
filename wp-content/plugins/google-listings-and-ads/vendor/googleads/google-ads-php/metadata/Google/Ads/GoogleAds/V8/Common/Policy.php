<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v8/common/policy.proto

namespace GPBMetadata\Google\Ads\GoogleAds\V8\Common;

class Policy
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
;google/ads/googleads/v8/enums/policy_topic_entry_type.protogoogle.ads.googleads.v8.enums"�
PolicyTopicEntryTypeEnum"�
PolicyTopicEntryType
UNSPECIFIED 
UNKNOWN

PROHIBITED
LIMITED
FULLY_LIMITED
DESCRIPTIVE

BROADENING
AREA_OF_INTEREST_ONLYB�
!com.google.ads.googleads.v8.enumsBPolicyTopicEntryTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
Wgoogle/ads/googleads/v8/enums/policy_topic_evidence_destination_mismatch_url_type.protogoogle.ads.googleads.v8.enums"�
1PolicyTopicEvidenceDestinationMismatchUrlTypeEnum"�
-PolicyTopicEvidenceDestinationMismatchUrlType
UNSPECIFIED 
UNKNOWN
DISPLAY_URL
	FINAL_URL
FINAL_MOBILE_URL
TRACKING_URL
MOBILE_TRACKING_URLB�
!com.google.ads.googleads.v8.enumsB2PolicyTopicEvidenceDestinationMismatchUrlTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
Xgoogle/ads/googleads/v8/enums/policy_topic_evidence_destination_not_working_device.protogoogle.ads.googleads.v8.enums"�
2PolicyTopicEvidenceDestinationNotWorkingDeviceEnum"q
.PolicyTopicEvidenceDestinationNotWorkingDevice
UNSPECIFIED 
UNKNOWN
DESKTOP
ANDROID
IOSB�
!com.google.ads.googleads.v8.enumsB3PolicyTopicEvidenceDestinationNotWorkingDeviceProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
`google/ads/googleads/v8/enums/policy_topic_evidence_destination_not_working_dns_error_type.protogoogle.ads.googleads.v8.enums"�
8PolicyTopicEvidenceDestinationNotWorkingDnsErrorTypeEnum"�
4PolicyTopicEvidenceDestinationNotWorkingDnsErrorType
UNSPECIFIED 
UNKNOWN
HOSTNAME_NOT_FOUND
GOOGLE_CRAWLER_DNS_ISSUEB�
!com.google.ads.googleads.v8.enumsB9PolicyTopicEvidenceDestinationNotWorkingDnsErrorTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
+google/ads/googleads/v8/common/policy.protogoogle.ads.googleads.v8.commonWgoogle/ads/googleads/v8/enums/policy_topic_evidence_destination_mismatch_url_type.protoXgoogle/ads/googleads/v8/enums/policy_topic_evidence_destination_not_working_device.proto`google/ads/googleads/v8/enums/policy_topic_evidence_destination_not_working_dns_error_type.protogoogle/api/annotations.proto"n
PolicyViolationKey
policy_name (	H �
violating_text (	H�B
_policy_nameB
_violating_text"�
PolicyValidationParameter
ignorable_policy_topics (	X
exempt_policy_violation_keys (22.google.ads.googleads.v8.common.PolicyViolationKey"�
PolicyTopicEntry
topic (	H �Z
type (2L.google.ads.googleads.v8.enums.PolicyTopicEntryTypeEnum.PolicyTopicEntryTypeF
	evidences (23.google.ads.googleads.v8.common.PolicyTopicEvidenceJ
constraints (25.google.ads.googleads.v8.common.PolicyTopicConstraintB
_topic"�

PolicyTopicEvidenceW
website_list (2?.google.ads.googleads.v8.common.PolicyTopicEvidence.WebsiteListH Q
	text_list (2<.google.ads.googleads.v8.common.PolicyTopicEvidence.TextListH 
language_code	 (	H h
destination_text_list (2G.google.ads.googleads.v8.common.PolicyTopicEvidence.DestinationTextListH g
destination_mismatch (2G.google.ads.googleads.v8.common.PolicyTopicEvidence.DestinationMismatchH l
destination_not_working (2I.google.ads.googleads.v8.common.PolicyTopicEvidence.DestinationNotWorkingH 
TextList
texts (	
WebsiteList
websites (	0
DestinationTextList
destination_texts (	�
DestinationMismatch�
	url_types (2~.google.ads.googleads.v8.enums.PolicyTopicEvidenceDestinationMismatchUrlTypeEnum.PolicyTopicEvidenceDestinationMismatchUrlType�
DestinationNotWorking
expanded_url (	H��
device (2�.google.ads.googleads.v8.enums.PolicyTopicEvidenceDestinationNotWorkingDeviceEnum.PolicyTopicEvidenceDestinationNotWorkingDevice#
last_checked_date_time (	H��
dns_error_type (2�.google.ads.googleads.v8.enums.PolicyTopicEvidenceDestinationNotWorkingDnsErrorTypeEnum.PolicyTopicEvidenceDestinationNotWorkingDnsErrorTypeH 
http_error_code (H B
reasonB
_expanded_urlB
_last_checked_date_timeB
value"�
PolicyTopicConstraintn
country_constraint_list (2K.google.ads.googleads.v8.common.PolicyTopicConstraint.CountryConstraintListH g
reseller_constraint (2H.google.ads.googleads.v8.common.PolicyTopicConstraint.ResellerConstraintH z
#certificate_missing_in_country_list (2K.google.ads.googleads.v8.common.PolicyTopicConstraint.CountryConstraintListH �
+certificate_domain_mismatch_in_country_list (2K.google.ads.googleads.v8.common.PolicyTopicConstraint.CountryConstraintListH �
CountryConstraintList%
total_targeted_countries (H �Z
	countries (2G.google.ads.googleads.v8.common.PolicyTopicConstraint.CountryConstraintB
_total_targeted_countries
ResellerConstraintI
CountryConstraint
country_criterion (	H �B
_country_criterionB
valueB�
"com.google.ads.googleads.v8.commonBPolicyProtoPZDgoogle.golang.org/genproto/googleapis/ads/googleads/v8/common;common�GAA�Google.Ads.GoogleAds.V8.Common�Google\\Ads\\GoogleAds\\V8\\Common�"Google::Ads::GoogleAds::V8::Commonbproto3'
        , true);
        static::$is_initialized = true;
    }
}

