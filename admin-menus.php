<?php
/*
Plugin Name: Admin Menu Management
Plugin URI: http://www.skullbit.com/admin-menu-management/
Description: Hide Admin pages from user levels.
Author: Skullbit
Version: 1.1
Author URI: http://www.skullbit.com
*/

if( !class_exists( 'AdminMenuPlugin' ) ){
	class AdminMenuPlugin {
		function AdminMenuPlugin(){
			add_action( 'admin_menu', array($this, 'MenuOverride') );
			#Add Settings Panel
			add_action( 'admin_menu', array($this, 'AddPanel') );
			#Update Settings on Save
			if( $_POST['action'] == 'admin_menu_update' )
				add_action( 'init', array($this,'SaveSettings') );
		}
		
		function AddPanel(){
			add_options_page( 'Admin Menu Management', 'Admin Menu Management', 10, 'admin-menu-management', array($this, 'Settings') );
		}
		
		function MenuOverride(){
			global $menu, $submenu;
			$amm = get_option( 'admin_menu_management' );		
			if( get_option( 'amm_menu' ) ):
				$menu = get_option( 'amm_menu' );
				#Add Comment balloon
				$awaiting_mod = wp_count_comments();
				$awaiting_mod = $awaiting_mod->moderated;
				$menu[20] = array( sprintf( $menu[20][0].' %s', "<span id='awaiting-mod' class='count-$awaiting_mod'><span class='comment-count'>$awaiting_mod</span></span>" ), $menu[20][1], $menu[20][2]);
				#Add Plugin Balloon
				$update_plugins = get_option( 'update_plugins' );
				$update_count = count( $update_plugins->response );
				$menu[35] = array( sprintf( $menu[35][0].' %s', "<span id='update-plugins' class='count-$update_count'><span class='plugin-count'>" . number_format_i18n($update_count) . "</span></span>" ), $menu[35][1], $menu[35][2]);
				#Add Users
				if ( current_user_can('edit_users') )
					$menu[40] = array($menu[41][0], $menu[41][1], $menu[41][2]);
					$menu[41] = array();

			endif;
			if( get_option( 'amm_submenu' ) )
				$submenu = get_option( 'amm_submenu' );		
			
		}
		
		function SaveSettings(){
			check_admin_referer('adminmenu-update-options');

			foreach( $this->MenuArr('menu') as $k=>$mn ):
					$newmenu[$k] = array( $_POST['name-'.$k], $_POST['ability-'.$k], $_POST['page-'.$k] );
			endforeach;
			foreach( $this->MenuArr('submenu') as $ky=>$smn ):
				$key = str_replace('.php', '', $ky);
				foreach( $smn as $k=>$nm ):
						$newsubmenu[$ky][$k] = array( $_POST['subname-'.$key.'-'.$k], $_POST['subability-'.$key.'-'.$k], $_POST['subpage-'.$key.'-'.$k] );
				endforeach;
			endforeach;
			update_option( 'amm_menu', $newmenu );
			update_option( 'amm_submenu', $newsubmenu );
			//echo '<pre>'; print_r($_POST); echo '</pre>';
			$_POST['notice'] = __('Settings Saved', 'regplus');
		}
		
		function Settings(){
			$amm = get_option( 'admin_menu_management' );
			$newm = get_option( 'amm_menu' );
			$newsm = get_option( 'amm_submenu' );
			if( !$newm ):
				$menuarr = $this->MenuArr('menu');
				$subarr = $this->MenuArr('submenu');
			else:
				$menuarr = $newm;
				$subarr = $newsm;
			endif;
			//print_r($menuarr);
			?>
            <div class="wrap">
            <h2><?php _e('Admin Menu Management Settings', 'regplus')?></h2>
                <form method="post" action="">
                	<?php if( function_exists( 'wp_nonce_field' )) wp_nonce_field( 'adminmenu-update-options'); ?>

                     <h3>Main Menu</h3>
                     <table class="form-table">
                        <tbody>
                        	<?php
							foreach( $menuarr as $k=>$mn ):
							?>
                        	<tr valign="top">
                       			 <th scope="row"><label><?php echo $mn[0];?></label></th>
                        		<td><label for="name-<?php echo $k;?>">Name:</label> <input type="text" name="name-<?php echo $k;?>" id="name-<?php echo $k;?>" value="<?php echo $mn[0];?>" />
                                <label for="ability-<?php echo $k;?>">Ability:</label> <select name="ability-<?php echo $k;?>" id="ability-<?php echo $k;?>"><?php echo $this->user_cap_dropdown($mn[1]);?></select> 
                                <label for="page-<?php echo $k;?>">Page:</label> <input type="text" name="page-<?php echo $k;?>" id="page-<?php echo $k;?>" value="<?php echo $mn[2];?>" /> 
                                </td>
                        	</tr>
                       <?php    endforeach; ?>
                        </tbody>
                     </table>
                     <h3>Sub Menus</h3>                     
                        	<?php
							foreach( $subarr as $ky=>$smn ):
								echo '<h4>'.$ky.'</h4>';
								echo '<table class="form-table">
                        <tbody>';
							$key = str_replace('.php', '', $ky);
								foreach( $smn as $k=>$mn ):
							?>
                        	<tr valign="top">
                       			 <th scope="row"><label><?php echo $mn[0];?></label></th>
                        		<td><label for="subname-<?php echo $key.'-'.$k;?>">Name:</label> <input type="text" name="subname-<?php echo $key.'-'.$k;?>" id="subname-<?php echo $key.'-'.$k;?>" value="<?php echo $mn[0];?>" />
                                <label for="subability-<?php echo $key.'-'.$k;?>">Ability:</label> <select name="subability-<?php echo $key.'-'.$k;?>" id="subability-<?php echo $key.'-'.$k;?>"><?php echo $this->user_cap_dropdown($mn[1]);?></select> 
                                <label for="subpage-<?php echo $key.'-'.$k;?>">Page:</label> <input type="text" name="subpage-<?php echo $key.'-'.$k;?>" id="subpage-<?php echo $key.'-'.$k;?>" value="<?php echo $mn[2];?>" /> 
                                </td>
                        	</tr>
                       <?php  
					   endforeach;
					   echo '</tbody>
                     </table>';
					 endforeach; ?>
                        
                     
                    <p class="submit"><input name="Submit" value="<?php _e('Save Changes','regplus');?>" type="submit" />
                    <input name="action" value="admin_menu_update" type="hidden" />
                </form>
              
            </div>
            <?php
		}
		
		
		function MenuArr($type='menu'){
            
$menu[0] = array(__('Dashboard'), 'read', 'index.php');
$menu[5] = array(__('Write'), 'edit_posts', 'post-new.php');
$menu[10] = array(__('Manage'), 'edit_posts', 'edit.php');
$menu[15] = array(__('Design'), 'switch_themes', 'themes.php');
$menu[20] = array(__('Comments'), 'edit_posts', 'edit-comments.php');
$menu[30] = array(__('Settings'), 'manage_options', 'options-general.php');
$menu[35] = array(__('Plugins'), 'activate_plugins', 'plugins.php');
$menu[41] = array(__('Users'), 'edit_users', 'users.php');
$menu[40] = array(__('Profile'), 'read', 'profile.php');

$submenu['post-new.php'][5] = array(__('Post'), 'edit_posts', 'post-new.php');
$submenu['post-new.php'][10] = array(__('Page'), 'edit_pages', 'page-new.php');
$submenu['post-new.php'][15] = array(__('Link'), 'manage_links', 'link-add.php');

$submenu['edit-comments.php'][5] = array(__('Comments'), 'edit_posts', 'edit-comments.php');

$submenu['edit.php'][5] = array(__('Posts'), 'edit_posts', 'edit.php');
$submenu['edit.php'][10] = array(__('Pages'), 'edit_pages', 'edit-pages.php');
$submenu['edit.php'][15] = array(__('Links'), 'manage_links', 'link-manager.php');
$submenu['edit.php'][20] = array(__('Categories'), 'manage_categories', 'categories.php');
$submenu['edit.php'][25] = array(__('Tags'), 'manage_categories', 'edit-tags.php');
$submenu['edit.php'][30] = array(__('Link Categories'), 'manage_categories', 'edit-link-categories.php');
$submenu['edit.php'][35] = array(__('Media Library'), 'upload_files', 'upload.php');
$submenu['edit.php'][40] = array(__('Import'), 'import', 'import.php');
$submenu['edit.php'][45] = array(__('Export'), 'import', 'export.php');

$submenu['users.php'][5] = array(__('Authors &amp; Users'), 'edit_users', 'users.php');
$submenu['users.php'][10] = array(__('Your Profile'), 'read', 'profile.php');
$submenu['profile.php'][5] = array(__('Your Profile'), 'read', 'profile.php');

$submenu['options-general.php'][10] = array(__('General'), 'manage_options', 'options-general.php');
$submenu['options-general.php'][15] = array(__('Writing'), 'manage_options', 'options-writing.php');
$submenu['options-general.php'][20] = array(__('Reading'), 'manage_options', 'options-reading.php');
$submenu['options-general.php'][25] = array(__('Discussion'), 'manage_options', 'options-discussion.php');
$submenu['options-general.php'][30] = array(__('Privacy'), 'manage_options', 'options-privacy.php');
$submenu['options-general.php'][35] = array(__('Permalinks'), 'manage_options', 'options-permalink.php');
$submenu['options-general.php'][40] = array(__('Miscellaneous'), 'manage_options', 'options-misc.php');

$submenu['plugins.php'][5] = array(__('Plugins'), 'activate_plugins', 'plugins.php');
$submenu['plugins.php'][10] = array(__('Plugin Editor'), 'edit_plugins', 'plugin-editor.php');

$submenu['themes.php'][5] = array(__('Themes'), 'switch_themes', 'themes.php');
$submenu['themes.php'][10] = array(__('Theme Editor'), 'edit_themes', 'theme-editor.php');
           
		   if( $type == 'menu' )
		   		return $menu;
			else if( $type = 'submenu' )
				return $submenu;
		}
		
		function user_cap_dropdown($default=''){
			$user_cap = array(
		'switch_themes',
        'edit_themes',
        'activate_plugins',
        'edit_plugins',
        'edit_users',
        'edit_files',
        'manage_options',
        'moderate_comments',
        'manage_categories',
        'manage_links',
        'upload_files',
        'import',
        'unfiltered_html',
        'edit_posts',
        'edit_others_posts',
        'edit_published_posts',
        'publish_posts',
        'edit_pages',
        'read',
        'edit_others_pages',
        'edit_published_pages',
        'edit_published_pages',
        'delete_pages',
        'delete_others_pages',
        'delete_published_pages',
        'delete_posts',
        'delete_others_posts',
        'delete_published_posts',
        'delete_private_posts',
        'edit_private_posts',
        'read_private_posts',
        'delete_private_pages',
        'edit_private_pages',
        'read_private_pages',
        'delete_users',
        'create_users',
        'unfiltered_upload',
        'edit_dashboard',
        'update_plugins',
        'delete_plugins',
        'level_10',
        'level_9',
        'level_8',
        'level_7',
        'level_6',
        'level_5',
        'level_4',
        'level_3',
        'level_2',
        'level_1',
        'level_0'
							  );
			foreach( $user_cap as $cap ):
				$output .= '<option name="'.$cap.'"';
				if( $cap == $default ) $output .= ' selected="selected"';
				$output .= '>'.$cap.'</option>'. "\n";
			endforeach;
			return $output;
		}

	}
}//END Class AdminMenuPlugin

if( class_exists( 'AdminMenuPlugin' ) )
	$adminmenuplug = new AdminMenuPlugin();

?>