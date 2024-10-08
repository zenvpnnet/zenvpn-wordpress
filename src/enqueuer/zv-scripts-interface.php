<?php

namespace zenVPN\Enqueuer;

interface ZV_Scripts_Interface {
	/**
	 * Enqueue scripts and styles.
	 *
	 * @param string $hook_suffix hook suffix.
	 * @return void
	 */
	public function enqueue_scripts( string $hook_suffix ): void;
}
