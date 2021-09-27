<?php

namespace Themeshark_Elementor\Controls\Query_Control;

if (!defined('ABSPATH')) exit;

use Elementor\Control_Select2;
use Themeshark_Elementor\Inc\Helpers;

class Query extends Control_Select2
{
	public function get_type()
	{
		return 'query';
	}

	protected function get_default_settings()
	{
		return array_merge(parent::get_default_settings(), [
			'query' => '',
		]);
	}

	public function enqueue()
	{
		wp_enqueue_script('query', Helpers::get_dir_url(__DIR__, 'query.js'));
	}
}
