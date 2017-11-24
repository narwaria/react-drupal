<?php

namespace Drupal\demo_rest_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Menu\MenuTreeParameters;

/**
 * Provides a Demo Resource
 *
 * @RestResource(
 *   id = "demo_resource",
 *   label = @Translation("Demo Resource"),
 *   uri_paths = {
 *     "canonical" = "/demo_rest_api/demo_resource"
 *   }
 * )
 */
class DemoResource extends ResourceBase {
  /**
     * Responds to entity GET requests.
     * @return \Drupal\rest\ResourceResponse
     */
    public function get() {
      $main =  $this->getDrupalMenu('main');
      return new ResourceResponse($main);
    }
    /**
   * Function prepare menu structured array.
   *
   * @param string $menu_name
   *   Menu name.
   *
   * @return array
   *   menu array
   */
  public function getDrupalMenu($menu_name = NULL) {
    $menu_tree = \Drupal::service('menu.link_tree');
    $parameters = new MenuTreeParameters();
    $parameters->setMaxDepth(10)->onlyEnabledLinks()->excludeRoot();
    $tree = $menu_tree->load($menu_name, $parameters);
    $menuLinks = [];
    foreach ($tree as $item) {
      $menuLinks[] = $this->buildMenutTree($item);
    }
    return $this->multidSort($menuLinks, 'weight');
  }

  /**
   * Function render the single menu item.
   *
   * @param object $item
   *   Menu item.
   *
   * @return array
   *   Return menu array.
   */
  private function buildMenutTree($item = NULL) {
    /** @var \Drupal\Core\Menu\MenuLinkInterface $link */
    $linkName = $item->link->getTitle();
    $linkDescription = $item->link->getDescription();
    if ($item->link->getUrlObject()->isExternal()) {
      $linkUrl = $item->link->getUrlObject()->getUri();
    }
    else {
      $path = $item->link->getUrlObject()->getInternalPath();
      $linkUrl = \Drupal::service('path.alias_manager')->getAliasByPath('/' . $path);
    }
    $attributes = $item->link->getOptions();
    if ($item->hasChildren) {
      foreach ($item->subtree as $subitem) {
        $childrens[] = $this->buildMenutTree($subitem);
      }
    }
    return [
      'link' => $linkName,
      'description' => isset($linkDescription) ? $linkDescription : NULL,
      'url' => $linkUrl,
      'linkpath' => $path,
      'weight' => $item->link->getWeight(),
      'hasChild' => isset($childrens) ? TRUE : FALSE,
      'attributes' => isset($attributes['attributes']) ? $attributes['attributes'] : NULL,
      'childrens' => isset($childrens) ? $this->multidSort($childrens, 'weight') : [],
    ];
  }


    /**
   * Function help to sort the php array by value.
   *
   * @param array $arr
   *   Actual array.
   * @param string $index
   *   Value to sort the data.
   *
   * @return array
   *   Return sorted array.
   */
  private function multidSort($arr, $index) {
    $b = array();
    $c = array();
    foreach ($arr as $key => $value) {
      $b[$key] = $value[$index];
    }
    asort($b);
    foreach ($b as $key => $value) {
      $c[] = $arr[$key];
    }
    return $c;
  }
}
