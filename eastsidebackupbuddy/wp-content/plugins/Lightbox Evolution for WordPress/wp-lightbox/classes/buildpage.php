<?php
  $_SERVER['REQUEST_URI'] = remove_query_arg(array('subpage', 'action'), $_SERVER['REQUEST_URI']);

  $subpage = isset($_GET['subpage']) ? (int)$_GET['subpage'] : 0;
?>

    <div class="wrap">
    <div id="icon-themes" class="icon32"></div>
    <h2><?php echo $this->plugin['Title']; ?> Settings</h2>
    
    <div class="aerowrapper">

      <div class="aeropaneltabs">
        
        <ul class="aeropaneltabs">
        <?php foreach ((array)$this->plugin['options'][$subpage]['tabs'] as $i=>$tabs) : ?>
          <?php if ($tabs["type"]=="tab") : ?>
          <li <?php if ($i==0) : ?>class="active"<?php endif; ?>><a href="#<?php echo sanitize_title_with_dashes($tabs['name']);?>"><?php echo $tabs['name'];?></a></li>
          <?php endif; ?>
        <?php endforeach; ?>
        </ul>

        <div class="container <?php echo ((bool)$this->plugin['options'][$subpage]['dontsave']?"container_without_buttons":'');?>">
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI'] . "&subpage=" . $subpage; ?>">
         
          <?php foreach ((array)$this->plugin['options'][$subpage]['tabs'] as $i=>$tabs) : $j=0;?>
          <?php if ($tabs["type"]=="tab") : ?>
          <div class="aerotab <?php if ($i==0) : ?>active<?php else: ?>hidden<?php endif; ?>" id="tab_<?php echo sanitize_title_with_dashes($tabs['name']);?>">
          
          
            <?php foreach ((array)$tabs['options'] as $sections) : ?>

                <?php if ($sections['type']=='html'):?>
                <div class="section_html"><?php echo $sections['value'];?></div>

                <?php elseif ($sections['type']=='help') :?>
                <div class="section_html"><?php include($this->path(true) . $sections['value']); ?></div>

                <?php elseif ($sections['type']=='section') :?>
                <?php $j++;?>
                <div class="section <?php echo $j%2?"":"section_right";?>">
                  <h2><?php echo $sections['name'];?></h2>
                  <div class="section_option">
                    
                    <?php foreach ((array)$sections['options'] as $options) : ?>
                      <div class="option_<?php echo $options['type'];?>">
                      <?php if (isset($options['label']) && $options['label']) : ?><label><?php echo $options['label'];?></label><?php endif; ?>
                    
                      <?php if ($options['type'] == 'text') : ?>
                        <input type="text" class="text" name="<?php echo "{$this->plugin['shortname']}_{$options['id']}";?>" maxlength="<?php echo "{$options['maxsize']}";?>" value="<?php echo ($this->get_option($options['id']) != "") ? $this->get_option($options['id']) : $options['default'] ; ?>" />

                      <?php elseif ($options['type'] == 'textarea') : ?>
                        <textarea class="textarea" cols="" rows="" name="<?php echo "{$this->plugin['shortname']}_{$options['id']}";?>"><?php echo ($this->get_option($options['id']) != "") ? $this->get_option($options['id']) : $options['default'] ; ?></textarea>
                      
                      <?php elseif ($options['type'] == 'select') : ?>
                        <select name="<?php echo "{$this->plugin['shortname']}_{$options['id']}";?>">
                          <?php foreach ((array)$options['options'] as $select_label=>$select_value) : ?>
                          <option value="<?php echo $select_value;?>" <?php if (($this->get_option($options['id']) != "" && $this->get_option($options['id']) == $select_value) || ($this->get_option($options['id']) == "" && $options['default'] == $select_value)) { echo 'selected="selected"'; } ?>><?php echo $select_label;?></option>
                          <?php endforeach; ?>
                        </select>

                      <?php elseif ($options['type'] == 'html') : ?>
                        <div class="section_html"><?php echo $options['value'];?></div>

                      <?php elseif ($options['type'] == 'button') : ?>
                        <a class="submit submit-blue" href="#"><?php echo $options['value'];?></a>


                      <?php endif; ?>
                      </div>
                    <?php endforeach; ?>

                  </div>
                </div>

                <?php elseif ($this->addons[$sections['type']]) :?>
                  <?php include($this->addons[$sections['type']]); ?>

                <?php endif;?>
            <?php endforeach; ?>

          
          
          </div>
          <?php endif; ?>
          <?php endforeach; ?>

          <?php if (!isset($this->plugin['options'][$subpage]['dontsave']) || !(bool)$this->plugin['options'][$subpage]['dontsave']):?>
          <div class="buttons">
            <div class="buttons_restore">
              <a href="<?php echo $_SERVER['REQUEST_URI']."&action=reset";?>" class="submit-grey">Restore Default Options</a>
            </div>
            <div class="buttons_submit">
              <a href="<?php echo $_SERVER['REQUEST_URI']."&action=save";?>" class="submit submit-blue">Save Changes</a>
            </div>
          </div>
          <?php endif; ?>
          
        </form>
        </div>
        
      </div><!--//aeropaneltabs-->

      <p><img src="<?php echo $this->path(); ?>/images/aeropanel.png" alt=""/></p>

      <div class="options">
        <ul>
          <?php foreach ((array)$this->plugin['options'] as $id=>$subpages) : ?>
          <li><a href="<?php echo $_SERVER['REQUEST_URI'] . "&subpage=" . $id; if ($subpages['hash']) { echo "#".$subpages['hash']; } ?>" <?php if ($id==$subpage) : ?>class="active"<?php endif; ?>><?php echo $subpages['name'];?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      
      <div class="options">
        <p>PHP Version: <?php echo phpversion();?></p>
        <p>Panel Version: <?php echo $this->version;?></p>
        <p><?php echo $this->plugin['type'];?> Version: <?php echo $this->plugin['Version'];?></p>
      </div>
      
    </div><!--// aerowrapper-->

    </div><!--// wrap-->