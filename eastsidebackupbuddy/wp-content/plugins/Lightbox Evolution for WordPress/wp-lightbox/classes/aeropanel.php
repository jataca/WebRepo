<?PHP
// mod sep 12 2011

class lbe_plugin_aeropanel
{
  var $plugin  = array();
  var $version = "1.1.8";
  var $styles  = array();
  var $scripts = array();
  var $addons  = array();
  
  function lbe_plugin_aeropanel($plugin='')
  {
    $this->plugin = (array)$plugin;
    
    if (!get_option($this->plugin['shortname']."_installed"))
    {
      foreach ($this->get_options($this->plugin['options']) as $value)
      {
        $type = $value['type'];
        if ($type=="text" || $type=="textarea" || $type=="select" || $type=="checkbox" )
        {
          $this->add_option($value['id'], $value['default']);
        }
      }
      $this->add_option('installed', true);
    }

    register_deactivation_hook($this->plugin['file'], array($this, 'unistall'));

    add_action('admin_init', array($this, 'admin_init'));
    add_action('admin_menu', array($this, 'admin_menu'));
    if (isset($_REQUEST['action']) && $_REQUEST['action']!='') $this->save();
  }

  function path($file='')
  {
    if ($file == '')
    {
      $file = $this->plugin['file'];
    }
    if ($file === true)
    {
      return plugin_dir_path($this->plugin['file']);
    }
    else
    {
      return plugins_url(plugin_basename(dirname($file)));
    }
  }

  function admin_init()
  {
    wp_register_style($this->plugin['shortname'] . "_aeropanel", $this->path() . "/css/style.css", false, $this->plugin['Version'], "all");
    wp_register_script($this->plugin['shortname'] . "_aeropanel", $this->path() . "/js/aeropanel/panel.js", false, $this->plugin['Version']);
  }

  function admin_styles()
  {
    wp_enqueue_style($this->plugin['shortname'] . "_aeropanel");
    wp_enqueue_script($this->plugin['shortname'] . "_aeropanel");

    foreach((array)$this->styles as $style)
    {
      wp_enqueue_style(basename($style), $style, false, $this->plugin['Version']);
    }

    foreach((array)$this->scripts as $script)
    {
      wp_enqueue_script(basename($script), $script, false, $this->plugin['Version']);
    }
  }
  
  function queue($file, $type)
  {
    if ($type == 'style')
    {
      $this->styles[] = $file;
    }
    else if ($type == 'script')
    {
      $this->scripts[] = $file;
    }
  }
  
  function addon($file, $type)
  {
    $this->addons[$type] = $file;
  }

  function add_option($option, $value)
  {
    return add_option($this->plugin['shortname']."_$option", $value);
  }    
  
  function save_option($option, $value)
  {
    return update_option($this->plugin['shortname']."_$option", $value);
  }
  
  function get_option($option)
  {
    $temp_option = get_option($this->plugin['shortname']."_$option");
    return is_array($temp_option) ? $temp_option : stripslashes($temp_option);
  }
  
  function get_options($array, $out=null)
  {
    $out = (array) $out;
    foreach($array as $key => $child)
    {
      if (is_array($child))
      {
        if ((isset($child['type']) && $child['type'] != '') && (isset($child['id']) && $child['id'] != ''))
        {
          $out[] = $child;
        }
        else
        {
          $out = $this->get_options($child, $out);
        }
      }
    }
    return $out;
  }
  
  function delete_option($option)
  {
    return delete_option($this->plugin['shortname']."_$option");
  }

  /*!
   * Add a new submenu under Settings
   */
  function admin_menu()
  {
    if ( current_user_can('manage_options') )
    {
      $this->plugin = array_merge($this->plugin, get_plugin_data($this->plugin['file'], false, false));
    }
    if (function_exists('add_options_page'))
    {
      $page = add_options_page($this->plugin['Name'], $this->plugin['Title'], 'manage_options', basename($this->plugin['file']), array($this, 'admin'));
      add_action('admin_print_styles-' . $page, array($this, 'admin_styles'));
    }
  }

  /*!
   * Show the panel
   */  
  function admin()
  {
    $i=0;
    
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'save')
    {
      echo '<div id="message" class="updated fade"><p><strong>'.$this->plugin['Title'].' settings saved.</strong></p></div>';
    }
    
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'reset')
    {
      echo '<div id="message" class="updated fade"><p><strong>'.$this->plugin['Title'].' settings reset.</strong></p></div>';
    }
 
    include('buildpage.php');
  }
  
  /*!
   * Save options, and reset options
   */
  function save()
  {
    if (isset($_GET['page']) && $_GET['page'] == basename($this->plugin['file']) )
    {
      if ( 'save' == $_REQUEST['action'] )
      {
        foreach ($this->get_options($this->plugin['options']) as $value)
        {
          $value['id'] = $this->plugin['shortname'].'_'.$value['id'];
          if ( isset($_REQUEST[$value['id']]) )
          {
            update_option( $value['id'], $_REQUEST[ $value['id'] ]  );
          }
          else
          {
            delete_option( $value['id'] );
          }
        }
      }
      else if ( 'reset' == $_REQUEST['action'] )
      {
        foreach ($this->get_options($this->plugin['options']) as $value)
        {
          $this->delete_option( $value['id'] );
        }
        
        $this->delete_option( "installed" );
      }
    }
  }

  function unistall()
  {
    foreach ($this->get_options($this->plugin['options']) as $value)
    {
      $this->delete_option( $value['id'] );
    }
    
    $this->delete_option( "installed" );
  }

}

?>