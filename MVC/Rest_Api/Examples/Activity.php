<?php

namespace WSWD\Rest_Api;

use WSWD\Post_Types\Activities;
use WSWD\Taxonomies\ActivityCategories;
use WSWD\Taxonomies\AgeRange;
use WSWD\Taxonomies\Type;

/**
 * Activity
 *
 * @author  Mat Lipe
 * @since   1/11/2017
 *
 * @package WSWD\Rest_Api
 */
class Activity extends PostAbstract {

	const POST_TYPE = \WSWD\Post_Types\Activity::POST_TYPE;

	protected $taxonomies = [
		'category',
		'post_tag',
		ActivityCategories::NAME,
		Type::NAME,
		AgeRange::NAME,
	];

	protected $allowed_meta_keys = [
		Activities::COST_META_KEY,
		Activities::SEASONS_META_KEY,
		Activities::HANDICAP_META_KEY,
		Activities::FEATURED_META_KEY,
	];

}