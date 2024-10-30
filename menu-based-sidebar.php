<?php
/*
Plugin Name: Menu Based Sidebar
Description: Most popular plugin that give a smart way display menu in sidebar.
Author: WP-EXPERTS.IN Team
Author URI: https://www.wp-experts.in
Version: 1.6
License GPL2
Copyright 2023  WP-Experts.IN  (email  raghunath.0087@gmail.com)
This program is free software; you can redistribute it andor modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if( ! class_exists( 'MenuBasedSidebar' ) ) {
    class MenuBasedSidebar {
        /**
         * Construct the plugin object
         */
        public function __construct() {
                        // Installation and uninstallation hooks
                        register_activation_hook( __FILE__,  array(&$this, 'mbs_activate') );
                        register_deactivation_hook( __FILE__,  array(&$this, 'mbs_deactivate') );
                        // admin settings links
                        add_filter( "plugin_action_links_".plugin_basename(__FILE__), array(&$this,'mbs_settings_link') );
            // add shortcode
                        add_shortcode( 'mbs_menu', array( &$this, 'mbs_shortcode_func') );
                       
        } // END public function __construct
               
		     public function get_current_submenu_item_id($menu_location) {
						   $menu_locations = get_nav_menu_locations();
						   						   
						   if (isset($menu_locations[$menu_location])) {
							$menu_object = get_term($menu_locations[$menu_location], 'nav_menu');
							$menu_items = wp_get_nav_menu_items($menu_object->term_id);
                            $current_menu = array( "id" =>0, "parent" =>0 );
							if (isset($menu_items)) {
							 foreach ($menu_items as $menu_item) {
								if ( isset($menu_item->object_id) && is_page( $menu_item->object_id ) ) {

									$current_menu['id'] = $menu_item->ID;
									$current_menu['parent'] = $menu_item->menu_item_parent;

									return $current_menu;
								}
							}
						   }
						   
						   return array( "id" =>0, "parent" =>0 );
						}

						
					}
		
                 public function mbs_get_child_menu($items, $pid, $location) {
					 
					 
                        $current_submenu_item = $this->get_current_submenu_item_id($location);

				 
                         $submenu = array();  // all menu items under $menuID
                          foreach($items as $item){
                                  if($item->menu_item_parent == $pid )
                                         $submenu[] = $item;
                          }
                         
                        $childhtml = '';
                        $currentPageId = get_queried_object_id(); // current page id
                         
             if (  isset( $submenu ) && ( is_array($submenu) || is_object($submenu))) { // if we found any
             
			 $childhtml .= '<ul class="subchild mbs-subchild">';
               
              foreach($submenu as $subitem){
				 
				  if( ( isset( $current_submenu_item['parent']) && $current_submenu_item['parent'] ==  $subitem->menu_item_parent ) || (isset( $current_submenu_item['id']) && $current_submenu_item['id'] ==  $subitem->menu_item_parent ) ) {
				  
                                  $class = ( $subitem->object_id == $currentPageId ) ? 'class="menu-item child-item active" ' : 'class="menu-item child-item"';
				  
				  $target = !empty( $subitem->target ) ? ' target="'.$subitem->target.'" ': '';
				  
                 $childhtml .= '<li '.$class.' id="subitem-'.$subitem->ID.'">
                                 <a href="'.$subitem->url.'" '. $target.'>'.$subitem->title.'</a></li>';
			  }
			  }
                  $childhtml .='</ul>';
           }
                         
                         return $childhtml;
                         
                 }
                /**
                 * add shortcode
                 */     
                public function mbs_shortcode_func( $atts ) {
                        $html = '';
                        if( !isset( $atts['menu_id'] ) )
                        return '';
					
					    $location = ( isset( $atts['location'] ) && !empty($atts['location'] ) ) ? $atts['location'] : 'main_navigation';
					
                          global $post;
                         
                          $menu_items = wp_get_nav_menu_items($atts['menu_id']);
                          $current_menu_id = null;
                          $currentPageId = $parentPageId = '';
						  if( isset( $post ) ) {
                          $currentPageId = $post->ID; 
						  $parentPageId = $post->post_parent; 
						  }
                          // get current top level menu item id
						  
						  if ( isset( $menu_items ) && (is_array($menu_items) || is_object($menu_items)) ) {
							  
							   $current_submenu_item = $this->get_current_submenu_item_id($location);

                          foreach ( $menu_items as $item ) {
                                
								if( ( isset( $current_submenu_item['parent']) && $current_submenu_item['parent'] ==  $item->ID ) || (isset( $current_submenu_item['id']) && $current_submenu_item['id'] ==  $item->ID ) ) {
                                  // if it's a top level page, set the current id as this page. if it's a subpage, set the current id as the parent
                                  $current_menu_id = ( $item->menu_item_parent ) ? $item->menu_item_parent : $item->ID;
                                       
                                  break;
                                }
                          }
						  }
                         
                          // uncomment this line if you don't want to display an empty ul
                          if ( $current_menu_id == null ) return;
                         
                          //echo "dd"; exit;
                          // display the submenu
                          $html .= '<ul id="menu-sidebar-services" class="menu service-child">';
                         
                        $currentPageId = get_queried_object_id(); // current page id
                       
					   if( isset( $menu_items ) && ( is_array($menu_items) || is_object($menu_items) )) {
                          foreach ( $menu_items as $item ) {
                                if ( $item->menu_item_parent == $current_menu_id ) {
									
									
                                  $class = ( $item->object_id == $currentPageId ) ? 'class="menu-item child-item active" ' : 'class="menu-item child-item"';
								  $target = !empty( $item->target ) ? ' target="'.$item->target.'" ': '';
									
                                  $html .= "<li {$class} id='mbs-item-".$item->object_id."'>
								  <a href='{$item->url}' {$target}>{$item->title}</a>";
                                 
                                     $loopPID = $item->object_id;
                                                 
                                     $html  .= $this->mbs_get_child_menu($menu_items, $item->ID, $location);
                                       
                                  $html.="</li>";
                                }
                                  $menuID = $item->ID;
                          }
					   }
                       
                       
                         
                          $html .= "</ul>";
                       
                       
                       
                       
                         
                          return $html;
                } // END public function mbs_shortcode_func()
        /**
         * Activate the plugin
         */
        public function mbs_activate() {
            // Do nothing
        } // END public static function activate
   
        /**
         * Deactivate the plugin
         */     
        public function mbs_deactivate() {
            // Do nothing
        } // END public static function deactivate
        // Add the contact link to the plugins page
                public function mbs_settings_link($links) { 
                        $settings_link = '<a href="https://www.wp-experts.in/contact-us?refrer=mbs-settings&source='.home_url().'" target="_blank">contact author</a>'; 
                        array_unshift($links, $settings_link); 
                        return $links; 
                }
    } // END class MenuBasedSidebar
} // END if(!class_exists('MenuBasedSidebar'))
if( class_exists( 'MenuBasedSidebar' ) ) {
    // instantiate the plugin class
    $init = new MenuBasedSidebar();
}