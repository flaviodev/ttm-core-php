<?php

namespace ttm\control;

interface Command {
	public function execute(array $args);
}