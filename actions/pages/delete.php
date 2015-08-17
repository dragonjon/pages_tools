<?php
/**
 * Remove a page
 *
 * Subpages are not deleted but are moved up a level in the tree
 *
 * @package ElggPages
 */

$guid = get_input('guid');
$page = get_entity($guid);
if (!pages_tools_is_valid_page($page)) {
	register_error(elgg_echo('pages:delete:failure'));
	forward(REFERER);
}

// get the container of the page (eg user/group)
$container = $page->getContainerEntity();

// only allow owners and (group)admin to delete
if (elgg_get_logged_in_user_guid() == $page->getOwnerGuid() || (!empty($container) && $container->canEdit())) {
	
	// Bring all child elements forward
	$parent = $page->parent_guid;
	$children = elgg_get_entities_from_metadata(array(
		'metadata_name' => 'parent_guid',
		'metadata_value' => $page->getGUID()
	));
	if ($children) {
		$db_prefix = elgg_get_config('dbprefix');
		$subtype_id = (int)get_subtype_id('object', 'page_top');
		$newentity_cache = is_memcache_available() ? new ElggMemcache('new_entity_cache') : null;

		foreach ($children as $child) {
			if ($parent) {
				$child->parent_guid = $parent;
			} else {
				// If no parent, we need to transform $child to a page_top
				$child_guid = (int)$child->guid;

				update_data("UPDATE {$db_prefix}entities
					SET subtype = $subtype_id WHERE guid = $child_guid");

				elgg_delete_metadata(array(
					'guid' => $child_guid,
					'metadata_name' => 'parent_guid',
				));

				_elgg_invalidate_cache_for_entity($child_guid);
				if ($newentity_cache) {
					$newentity_cache->delete($child_guid);
				}
			}
		}
	}

	if ($page->delete()) {
		system_message(elgg_echo('pages:delete:success'));
		if ($parent) {
			if ($parent = get_entity($parent)) {
				forward($parent->getURL());
			}
		}
		if (elgg_instanceof($container, 'group')) {
			forward("pages/group/$container->guid/all");
		} else {
			forward("pages/owner/$container->username");
		}
	}
}

register_error(elgg_echo('pages:delete:failure'));
forward(REFERER);
