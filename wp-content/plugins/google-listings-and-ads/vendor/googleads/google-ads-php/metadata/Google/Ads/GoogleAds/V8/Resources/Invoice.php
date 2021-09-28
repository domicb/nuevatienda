<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v8/resources/invoice.proto

namespace GPBMetadata\Google\Ads\GoogleAds\V8\Resources;

class Invoice
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
�
1google/ads/googleads/v8/enums/month_of_year.protogoogle.ads.googleads.v8.enums"�
MonthOfYearEnum"�
MonthOfYear
UNSPECIFIED 
UNKNOWN
JANUARY
FEBRUARY	
MARCH	
APRIL
MAY
JUNE
JULY

AUGUST	
	SEPTEMBER

OCTOBER
NOVEMBER
DECEMBERB�
!com.google.ads.googleads.v8.enumsBMonthOfYearProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
*google/ads/googleads/v8/common/dates.protogoogle.ads.googleads.v8.commongoogle/api/annotations.proto"W
	DateRange

start_date (	H �
end_date (	H�B
_start_dateB
	_end_date"�
YearMonthRange8
start (2).google.ads.googleads.v8.common.YearMonth6
end (2).google.ads.googleads.v8.common.YearMonth"d
	YearMonth
year (I
month (2:.google.ads.googleads.v8.enums.MonthOfYearEnum.MonthOfYearB�
"com.google.ads.googleads.v8.commonB
DatesProtoPZDgoogle.golang.org/genproto/googleapis/ads/googleads/v8/common;common�GAA�Google.Ads.GoogleAds.V8.Common�Google\\Ads\\GoogleAds\\V8\\Common�"Google::Ads::GoogleAds::V8::Commonbproto3
�
0google/ads/googleads/v8/enums/invoice_type.protogoogle.ads.googleads.v8.enums"\\
InvoiceTypeEnum"I
InvoiceType
UNSPECIFIED 
UNKNOWN
CREDIT_MEMO
INVOICEB�
!com.google.ads.googleads.v8.enumsBInvoiceTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
/google/ads/googleads/v8/resources/invoice.proto!google.ads.googleads.v8.resources0google/ads/googleads/v8/enums/invoice_type.protogoogle/api/field_behavior.protogoogle/api/resource.protogoogle/api/annotations.proto"�
Invoice?
resource_name (	B(�A�A"
 googleads.googleapis.com/Invoice
id (	B�AH �M
type (2:.google.ads.googleads.v8.enums.InvoiceTypeEnum.InvoiceTypeB�A
billing_setup (	B�AH�%
payments_account_id (	B�AH�%
payments_profile_id (	B�AH�

issue_date (	B�AH�
due_date (	B�AH�J
service_date_range	 (2).google.ads.googleads.v8.common.DateRangeB�A
currency_code (	B�AH�/
"adjustments_subtotal_amount_micros (B�A*
adjustments_tax_amount_micros (B�A,
adjustments_total_amount_micros (B�A4
\'regulatory_costs_subtotal_amount_micros (B�A/
"regulatory_costs_tax_amount_micros (B�A1
$regulatory_costs_total_amount_micros (B�A(
subtotal_amount_micros! (B�AH�#
tax_amount_micros" (B�AH�%
total_amount_micros# (B�AH	�#
corrected_invoice$ (	B�AH
�
replaced_invoices% (	B�A
pdf_url& (	B�AH�f
account_budget_summaries (2?.google.ads.googleads.v8.resources.Invoice.AccountBudgetSummaryB�A�
AccountBudgetSummary
customer
 (	B�AH �+
customer_descriptive_name (	B�AH� 
account_budget (	B�AH�%
account_budget_name (	B�AH�\'
purchase_order_number (	B�AH�(
subtotal_amount_micros (B�AH�#
tax_amount_micros (B�AH�%
total_amount_micros (B�AH�T
billable_activity_date_range	 (2).google.ads.googleads.v8.common.DateRangeB�AB
	_customerB
_customer_descriptive_nameB
_account_budgetB
_account_budget_nameB
_purchase_order_numberB
_subtotal_amount_microsB
_tax_amount_microsB
_total_amount_micros:T�AQ
 googleads.googleapis.com/Invoice-customers/{customer_id}/invoices/{invoice_id}B
_idB
_billing_setupB
_payments_account_idB
_payments_profile_idB
_issue_dateB
	_due_dateB
_currency_codeB
_subtotal_amount_microsB
_tax_amount_microsB
_total_amount_microsB
_corrected_invoiceB

_pdf_urlB�
%com.google.ads.googleads.v8.resourcesBInvoiceProtoPZJgoogle.golang.org/genproto/googleapis/ads/googleads/v8/resources;resources�GAA�!Google.Ads.GoogleAds.V8.Resources�!Google\\Ads\\GoogleAds\\V8\\Resources�%Google::Ads::GoogleAds::V8::Resourcesbproto3'
        , true);
        static::$is_initialized = true;
    }
}

