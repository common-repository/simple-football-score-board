<?php
/*
Plugin Name: Simple Football Score Board
Plugin URI: https://php.dogrow.net/wordpressplugin/simple-football-scoreboard/
Description: Generate football scoreboard from shortcode
Version: 1.0
Author: DOGROW.NET
Author https://php.dogrow.net/
License: GPL2
*/
////////////////////////////////////////////////////////////////////////
if(class_exists('YTMRFBScoreBoard')){
  $obj = new YTMRFBScoreBoard();
}
////////////////////////////////////////////////////////////////////////
class YTMRFBScoreBoard {
  private $m_setting_group;
  private $m_option_name;
  private $m_options;
  //////////////////////////////////////////////////////////////////////
  public function __construct(){
    $this->m_setting_group = 'YTMRFBScoreBoard-setting-group';
    $this->m_option_name   = 'YTMRFBScoreBoard';
    $this->m_options = array('border_line_color'  =>array('t'=>'Border line color','v'=>'#bbbb00')
                            ,'border_line_width'  =>array('t'=>'Border line width','v'=>'3px')
                            ,'background_color'   =>array('t'=>'Background color', 'v'=>'#3f7d39')
                            ,'box_color'          =>array('t'=>'Score box color',  'v'=>'#285b2b')
                            ,'text_color_score'   =>array('t'=>'Text color (score)', 'v'=>'#ffffff')
                            ,'text_color_name'    =>array('t'=>'Text color (name)', 'v'=>'#ffffff')
                  );
    //------------------------------------------------------------------
    add_action('wp_head',    array($this,'proc_output_css'), 9999);
    add_action('admin_head', array($this,'proc_output_css'), 9999);
    //------------------------------------------------------------------
    add_shortcode('ytmr_fb_scoreboard', array($this, 'proc_shortcode'));
    add_filter('widget_text', 'do_shortcode');
    //------------------------------------------------------------------
    add_action('admin_menu', array($this, 'proc_create_menu'));
    //------------------------------------------------------------------
    add_action('admin_init', array($this,'proc_register_settings'));
    //------------------------------------------------------------------
    add_action('admin_enqueue_scripts', array($this, 'proc_add_script'));
    //------------------------------------------------------------------
    register_activation_hook(  __FILE__, array($this, 'proc_plugin_activate'));
    register_deactivation_hook(__FILE__, array($this, 'proc_plugin_deactivate'));
  }
  //////////////////////////////////////////////////////////////////////
  function proc_add_script() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('ytmr_simple_football_scoreboard_script', plugins_url('js/ytmr_simple_football_scoreboard.js', __FILE__), array('jquery'), '1.0.0', TRUE);
  }
  //////////////////////////////////////////////////////////////////////
  public function proc_plugin_activate(){
    add_option($this->m_option_name, $this->m_options);
  }
  //////////////////////////////////////////////////////////////////////
  public function proc_plugin_deactivate(){
    delete_option($this->m_option_name);
  }
  //////////////////////////////////////////////////////////////////////
  public function proc_output_css(){
    //------------------------------------------------------------------
    $ary_set = get_option($this->m_option_name);
    foreach($ary_set as &$ary_tv){
      $ary_tv['v'] = esc_attr($ary_tv['v']);
    }
    //------------------------------------------------------------------
echo <<< EOM

<style type="text/css">
div#YTMRFBScoreBoard{
  background-color: {$ary_set['background_color']['v']};
  border-color: {$ary_set['border_line_color']['v']};
  border-width: {$ary_set['border_line_width']['v']};
  border-style: solid;
  margin: 3px; padding: 3px;
}
div#YTMRFBScoreBoard table{
  background: transparent !important;
  border: none !important;
  margin:0 !important;
}
div#YTMRFBScoreBoard tr,
div#YTMRFBScoreBoard td{
  background: transparent !important;
  border: none !important;
  padding:0;
}
div#YTMRFBScoreBoard td{
  line-height: 1.5;
}
div#YTMRFBScoreBoard td#tm_name{
  color: {$ary_set['text_color_name']['v']};
}
div#YTMRFBScoreBoard table.detail{
  background: transparent !important;
  width: 100%;
}
div#YTMRFBScoreBoard div.inner{
  display: inline-table;
  padding: 4px 2px;
  background-color: {$ary_set['box_color']['v']};
}
div#YTMRFBScoreBoard div.inner td{
  color: {$ary_set['text_color_score']['v']};
}
@media (min-width: 450px) {
  div#YTMRFBScoreBoard #td_ttl1{
    text-align: right;
    padding-right: 1rem;
  }
  div#YTMRFBScoreBoard #td_ttl2{
    text-align: left;
    padding-left: 1rem;
  }
} /* media */
@media (max-width: 449px) {
  div#YTMRFBScoreBoard #td_ttl1,
  div#YTMRFBScoreBoard #td_ttl2{
    text-align: center;
  }
} /* media */
</style>

EOM;
    //------------------------------------------------------------------
  }
  //////////////////////////////////////////////////////////////////////
  public function proc_shortcode( $args ){
    return $this->sub_display_table($args);
  }
  //////////////////////////////////////////////////////////////////////
  public function proc_create_menu() {
    add_submenu_page('options-general.php', 'Simple FBScoreboard', 'Simple FBScoreboard', 'administrator', __FILE__, array($this, 'proc_display_settings_page'));
  }
  //////////////////////////////////////////////////////////////////////
  public function proc_register_settings() {
    register_setting($this->m_setting_group, $this->m_option_name, array($this, 'proc_handle_sanitization'));
  }
  //////////////////////////////////////////////////////////////////////
  public function proc_handle_sanitization($ary_set) {
    $ary_options = $this->m_options;
    foreach($ary_set as $key => &$ary_tv){
      $ary_options[$key]['v'] = esc_attr($ary_tv['v']);
    }
    return $ary_options;
  }
  //////////////////////////////////////////////////////////////////////
  public function proc_display_settings_page() {
    //------------------------------------------------------------------
    $ary_set = get_option($this->m_option_name);
    foreach($ary_set as &$ary_tv){
      $ary_tv['v'] = esc_attr($ary_tv['v']);
    }
    //------------------------------------------------------------------
    $ary_bw_sel = array('1px'=>'', '2px'=>'', '3px'=>'');
    $ary_bw_sel[$ary_set['border_line_width']['v']] = 'selected';
    //------------------------------------------------------------------
    $args = array('fsize'=>1, 'width'=>'400px', 'tm1'=>'Jokers', 'tm2'=>'Riots', 'scr'=>'2-0/1-3', 'pk'=>'5-2');
    $html_scrboard = $this->sub_display_table($args);
    //------------------------------------------------------------------
echo <<< EOM
<div class="wrap">
<h2>Simple Football Scoreboard</h2>
<h2>1. Usage</h2>
<p>Short code : <span style="background:#fff;color:#00f;padding:3px 5px;font-size:1.2rem">[ytmr_fb_scoreboard]</span></p>
<p>Parameters : <br />
- fsize : base font size [rem] (the smallest one)<br />
- width : whole width [px/rem/%]<br />
- tm1 : team name of the upper side<br />
- tm2 : team name of the lower side<br />
- scr : score ex) "1-0/0-2"<br />
- pk : PK score ex) "3-5"<br />
</p>
<p>sample : <span style="background:#fff;color:#00f;padding:3px 5px;font-size:1.2rem">[ytmr_fb_scoreboard fsize="1" width="300px" tm1="Jokers" tm2="Riots" scr="2-0/1-3" pk="5-2"]</span></p>
{$html_scrboard}
<h2 style="margin-top:2.5rem">2. Settings</h2>
<form id="YTMRFBScoreBoard_form" method="post" action="options.php">
EOM;
    settings_fields($this->m_setting_group);
    do_settings_sections($this->m_setting_group);
echo <<< EOM
  <table class="form-table">
    <tr>
      <th>{$ary_set['border_line_width']['t']}</th>
      <td>
        <select name="{$this->m_option_name}[border_line_width][v]">
          <option value="1px" {$ary_bw_sel['1px']}>thin</option>
          <option value="2px" {$ary_bw_sel['2px']}>middle</option>
          <option value="3px" {$ary_bw_sel['3px']}>thick</option>
        </select>
      </td>
    </tr>
    <tr>
      <th>{$ary_set['border_line_color']['t']}</th>
      <td>
        <input type="color" name="{$this->m_option_name}[border_line_color][v]" value="{$ary_set['border_line_color']['v']}">
      </td>
    </tr>
    <tr>
      <th>{$ary_set['background_color']['t']}</th>
      <td>
        <input type="color" name="{$this->m_option_name}[background_color][v]" value="{$ary_set['background_color']['v']}">
      </td>
    </tr>
    <tr>
      <th>{$ary_set['text_color_name']['t']}</th>
      <td>
        <input type="color" name="{$this->m_option_name}[text_color_name][v]" value="{$ary_set['text_color_name']['v']}">
      </td>
    </tr>
    <tr>
      <th>{$ary_set['box_color']['t']}</th>
      <td>
        <input type="color" name="{$this->m_option_name}[box_color][v]" value="{$ary_set['box_color']['v']}">
      </td>
    </tr>
    <tr>
      <th>{$ary_set['text_color_score']['t']}</th>
      <td>
        <input type="color" name="{$this->m_option_name}[text_color_score][v]" value="{$ary_set['text_color_score']['v']}">
      </td>
    </tr>
  </table>
EOM;
  submit_button();
echo <<< EOM
</form>
</div>
EOM;
  }
  //////////////////////////////////////////////////////////////////////
  public static function sub_disp_r_detail($ary_r, $is_pk, $addStyle){
    $mid = ($is_pk === TRUE)? 'PK' : '-';
return <<< EOM
<tr>
  <td style="text-align:right;padding-right:0.5rem;{$addStyle}">{$ary_r[0]}</td>
  <td style="text-align:center;width:1.5rem;{$addStyle}">{$mid}</td>
  <td style="text-align:left;padding-left:0.5rem;{$addStyle}">{$ary_r[1]}</td>
</tr>
EOM;
  }
  //////////////////////////////////////////////////////////////////////
  public static function sub_decode_score($scr){
    $ary_ret = array(0,0);
    //------------------------------------------------------------------
    $ary_part = explode('-', $scr);
    foreach($ary_part as $i => $v){
      $v = trim($v);
      if($v != ""){
        $ary_ret[$i] = $v;
      }
    }
    //------------------------------------------------------------------
tagEND:
    return $ary_ret;
  }
  //////////////////////////////////////////////////////////////////////
  // args['fsize'] : text size [rem]
  // args['width'] : whole width
  // args['tm1']   : team name #1
  // args['tm2']   : team name #2
  // args['scr']   : score     separator='/'  ex) 0-1/1-0
  // args['pk']    : score pk  ex) 3-5
  public function sub_display_table($args){
    $fsize   = (isset($args['fsize']))? $args['fsize'] : '1';
    $tm1     = (isset($args['tm1']))? $args['tm1'] : 'team1';
    $tm2     = (isset($args['tm2']))? $args['tm2'] : 'team2';
    $width   = (isset($args['width']))? $args['width'] : '100%';
    //------------------------------------------------------------------
    $fsize_M = $fsize * 1.5;
    $fsize_L = $fsize * 2.5;
    //------------------------------------------------------------------
    $size_S = 'font-size:'.$fsize.'rem !important;';
    $size_M = 'font-size:'.$fsize_M.'rem !important;';
    $size_L = 'font-size:'.$fsize_L.'rem !important;';
    //------------------------------------------------------------------
    // score
    $html_score = "<table class='detail'>";
    $ary_scr = array();
    $total_r1 = 0;
    $total_r2 = 0;
    $ary_part = explode('/', $args['scr']);
    foreach($ary_part as $apart){
      $ary_r = self::sub_decode_score($apart);
      $html_score .= self::sub_disp_r_detail($ary_r, FALSE, $size_S);
      $total_r1 += $ary_r[0];
      $total_r2 += $ary_r[1];
    }
    //------------------------------------------------------------------
    if(isset($args['pk'])){
      $ary_r = self::sub_decode_score($args['pk']);
      $html_score .= self::sub_disp_r_detail($ary_r, TRUE, $size_S);
    }
    //------------------------------------------------------------------
    $html_score .= "</table>";
    //------------------------------------------------------------------
return <<< EOM
<style type="text/css">
div#YTMRFBScoreBoard{
  ;
  max-width: 100% !important;
}
div#YTMRFBScoreBoard table.detail td{
  {$size_S}
}
</style>
<div id="YTMRFBScoreBoard" style="width: {$width} !important">
<table class="{$table_class}" style="width:100%">
<tr><td style="text-align:left;padding-left: 0.5rem;{$size_M}" id="tm_name">{$tm1}</td></tr>
<tr><td style="text-align:center"><div class="inner" style="width:80%;text-align:center">
  <table style="width:100%">
    <tr>
      <td style="vertical-align:middle;{$size_L}" id="td_ttl1">{$total_r1}</td>
      <td style="vertical-align:middle;width:30%" id="td_detail">{$html_score}</td>
      <td style="vertical-align:middle;{$size_L}" id="td_ttl2">{$total_r2}</td>
    </tr>
  </table>
  </div></td>
</tr>
<tr><td style="text-align:right;padding-right: 0.5rem;{$size_M}" id="tm_name">{$tm2}</td></tr>
</table></div>
EOM;
  }
}     // end of class
?>
