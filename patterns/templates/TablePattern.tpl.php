<?php
  $Vars = $Data->PatternVariables;
  
  if($Vars->form_title){
    echo "<h3>".t($Vars->form_title)."</h3>";
  }
  if ($Vars->before_text) {
    echo "<p>".t($Vars->before_text)."</p>\n";
  }
  if ($Vars->paginate && $Data->rows) {
      echo "\n\n<div id=\"pagination\">\n";
      $string = '';
      //$string .= t('Page Number').": ";
      if($Vars->page_number != 0) {
        $parameters = array(
            '__page_number' => $Vars->page_number-1,
            '__page_size' => $Vars->page_size,
          );
        $url = $Helper->createSelfUrl($parameters, TRUE);
        $string .="<a  class=\"previous\" href=\"".htmlspecialchars($url)."\" title=\"".t('Previous')."\"><span>&lt;&lt; ".t('Previous')."</span></a>\n";
      }else {
        $string .="<a  class=\"previous disabled\" href=\"javascript:void();\" title=\"".t('Previous')."\"><span>&lt;&lt; ".t('Previous')."</span></a>\n";
      }
      
      $parameters = array(
          '__page_number' => "replace_with_page_number",
          '__page_size' => $Vars->page_size,
        );
      $url = $Helper->createSelfUrl($parameters, TRUE);
      $string .= createComboBox(range(1,$Vars->pages), 'page_number', $Vars->page_number,"onchange=\"javascript:change_page(this, '".htmlspecialchars($url)."');\"");
      
      if($Vars->page_number != $Vars->pages - 1) {
        $parameters = array(
            '__page_number' => $Vars->page_number+1,
            '__page_size' => $Vars->page_size,
          );
        $url = $Helper->createSelfUrl($parameters, TRUE);
        $string .="<a class=\"next\" href=\"".htmlspecialchars($url)."\" title=\"".t('Next')."\"><span>".t('Next')." &gt;&gt;</span></a>\n";
      } else {
        $string .="<a class=\"next disabled\" href=\"javascript:void();\" title=\"".t('Next')."\"><span>".t('Next')." &gt;&gt;</span></a>\n";
      }
      
      $parameters = array(
          '__page_number' => $Vars->page_number,
          '__page_size' => 'replace_with_page_size',
        );
      $url = $Helper->createSelfUrl($parameters, TRUE);
      //$string .= t('Items per Page').": ";
      $page_sizes = array(
          '20' => '20',
          '50' => '50',
          '100' => '100',
          '200' => '200'
        );
      $string .= createComboBox($page_sizes, 'page_size', $Vars->page_size,"onchange=\"javascript:change_page_size(this, '".htmlspecialchars($url)."');\"");
      
      echo $string;
      echo "</div>\n";
    }
    if ( !empty($Data->filters)) {
      //echo "<pre>".htmlentities(print_r($Data->filters,1))."</pre>";
      
      echo "<form name='filters' method='get' action='' class='list_filters'><div>\n<strong>".t('Filter'). " &gt;&gt;</strong>\n";
      
      foreach($Data->filters AS $field => $filter){
        $Filter = (object)$filter;
        if($Filter->type=='custom'){
          echo $Filter->label.": ";
          $options = array();
          foreach($Filter->options AS $option) {
            $options[$option['value']] = $option['label'];
          }
          $selected = (!isset($Filter->selected))? $Filter->default:$Filter->selected;
          echo createComboBox($options, $field, $selected);
        }else if($Filter->type=='hidden'){
          ?> <input type='hidden' name='<?php echo $field ?>' value='<?php echo $Filter->value?>'/><?php
        }
        echo "\n";
      }
      echo "<input type=\"submit\" value=\"".t("Apply")."\"/>";
      echo "</div></form>";
      
   }
    if ( $Data->rows ) {
      echo "\n<table>\n";
      echo "<tr>";
      foreach($Data->fields as $field_title)
      {
        echo "<th>" . t($field_title) . "</th>";
      }
      if ( count($Data->actions) ) {
        echo "<th class=\"action\">".t('Actions')."</th>";
      }
      echo "</tr>\n";
      $count=0;
      foreach($Data->rows AS $row)
      {
        if ( ($count % 2) == 1 ){
          $class="odd";
        } else {
          $class="even";
        }
        echo "<tr class=\"$class\"";
        if($Data->prefix)
          echo " id=\"{$Data->prefix}_{$row[$Data->row_id]}\" ";
        echo ">";
        $count++;
        foreach($Data->fields as $field => $field_title)
        {
          
          if( isset($Data->links[$field]) ) {
            $link = (object)$Data->links[$field];
            if(strpos($link->action,'?') === FALSE) {
              echo "<td><a href=\"$link->action?$link->value={$row[$link->value]}\" title=\"$link->title\">".htmlspecialchars($row[$field])."</a></td>";
            } else {
              echo "<td><a href=\"$link->action&$link->value={$row[$link->value]}\" title=\"$link->title\">".htmlspecialchars($row[$field])."</a></td>";
            }
          } else {
            echo "<td>".htmlspecialchars($row[$field])."</td>";
          }
          
        }
        if ( !empty($Data->actions) ) {
          echo "<td class=\"action\">";
          foreach ( $Data->actions as $action)
          {
            $action = (object)$action;
            $action->title = t($action->title);
            if ( !$action->ajax) {
              if( !is_array($action->value) ) {
                if ( strpos($action->action,'?') === FALSE) {
                  echo "<a href=\"$action->action?";
                } else {
                  echo "<a href=\"$action->action&";
                }
                echo "$action->value={$row[$action->value]}\" title=\"$action->title\">";
                if ( !$action->icon ) {
                  echo "{$action->title}";
                } else {
                  $action->icon = $Helper->createFrameLink($action->icon, 1, 1);
                  echo "<img src=\"$action->icon\" alt=\"{$action->title}\"/>";
                }
              } else {
                if ( strpos($action->action,'?') === FALSE) {
                  echo "<a href=\"$action->action?";
                } else {
                  echo "<a href=\"$action->action&";
                }
                foreach($action->value as $single_value) {
                  echo "$single_value={$row[$single_value]}&";
                }
                echo "\" title=\"$action->title\">";
                
                if ( !$action->icon ) {
                  echo "{$action->title}";
                } else {
                  $action->icon = $Helper->createFrameLink($action->icon, 1, 1);
                  echo "<img src=\"$action->icon\" alt=\"{$action->title}\"/>";
                }
              }
              echo "</a> ";
            } else {
              echo "<a href=\"javascript:void(xajax_{$action->action}(";
              if ( !is_array($action->value) ) {
                echo "{$row[$action->value]}";
              } else {
                $values_array = array();
                foreach ($action->value AS $single_value) {
                  $values_array[]=$row[$single_value];
                }
                echo $values_string = implode(',',$values_array);
              }
              echo "));\" title=\"$action->title\">";
              if ( !$action->icon ) {
                echo "{$action->title}";
              } else {
                $action->icon = $Helper->createFrameLink($action->icon, 1, 1);
                echo "<img src=\"$action->icon\" alt=\"{$action->title}\"/>";
              }
              echo "</a> ";
            }
          }
          echo "</td>";
        }
        echo "</tr>\n";
      }
      echo "</table>\n";
    } else {
      if ($Vars->no_items_message) {
        echo "\n<p><strong>".t($Vars->no_items_message)."</strong></p>\n";
      } else {
        echo "\n<p><strong>".t("There are no items")."</strong\n</p>";
      }
    }
    if ($Vars->after_text) {
      echo "\n<p>$Vars->after_text</p>\n";
    }
    
    if ( !empty($Data->general_actions) ) {
    echo "<ul class=\"action\">";
    foreach ( $Data->general_actions as $action)
    {
      echo "<li>";
      $action = (object)$action;
      $action->title = t($action->title);
      $action->icon = $Helper->createFrameLink($action->icon, 1, 1);
      if ( !$action->ajax) {
        echo "<a href=\"$action->action\" title=\"$action->title\">";
        if ( !$action->icon ) {
          echo "{$action->title}";
        } else {
          echo "<img src=\"$action->icon\" alt=\"{$action->title}\"/>  {$action->title}";
        }
        echo "</a> ";
      } else {
        echo "<a href=\"javascript:void(xajax_{$action->action});\" title=\"$action->title\">";
        if ( !$action->icon ) {
          echo "{$action->title}";
        } else {
          echo "<img src=\"$action->icon\" alt=\"{$action->title}\"/> {$action->title}";
        }
        echo "</a> ";
      }
      echo "</li>\n";
    }
    echo "</ul>\n\n";
  }