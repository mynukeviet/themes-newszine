<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Sat, 10 Dec 2011 06:46:54 GMT
 */

if( ! defined( 'NV_MAINFILE' ) ) die( 'Stop!!!' );

if( ! nv_function_exists( 'nv_block_news_groups' ) )
{
	function nv_block_config_news_groups( $module, $data_block, $lang_block )
	{
		$module = 'news';
		$html_input = '';
		$html = '';
		$html .= '<tr>';
		$html .= '<td>' . $lang_block['blockid'] . '</td>';
		$html .= '<td><select name="config_blockid" class="form-control w200">';
		$html .= '<option value="0"> -- </option>';
		$sql = 'SELECT * FROM ' . NV_PREFIXLANG . '_news_block_cat ORDER BY weight ASC';
		$list = nv_db_cache( $sql, '', $module );
		foreach( $list as $l )
		{
			$html_input .= '<input type="hidden" id="config_blockid_' . $l['bid'] . '" value="' . NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module . '&amp;' . NV_OP_VARIABLE . '=' . $site_mods[$module]['alias']['groups'] . '/' . $l['alias'] . '" />';
			$html .= '<option alt="' . $linksite . '" value="' . $l['bid'] . '" ' . ( ( $data_block['blockid'] == $l['bid'] ) ? ' selected="selected"' : '' ) . '>' . $l['title'] . '</option>';
		}
		$html .= '</select>';
		$html .= $html_input;
		$html .= '<script type="text/javascript">';
		$html .= '	$("select[name=config_blockid]").change(function() {';
		$html .= '		$("input[name=title]").val($("select[name=config_blockid] option:selected").text());';
		$html .= '		$("input[name=link]").val($("#config_blockid_" + $("select[name=config_blockid]").val()).val());';
		$html .= '	});';
		$html .= '</script>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td>' . $lang_block['numrow'] . '</td>';
		$html .= '<td><input type="text" class="form-control w200" name="config_numrow" size="5" value="' . $data_block['numrow'] . '"/></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td>' . $lang_block['style'] . '</td>';
		$html .= '<td><select name="config_style" class="form-control w200">';
		$sel = $block_config['bellow_nav'] == 'bellow_nav' ? 'selected' : '';
		$html .= '<option value="bellow_nav" ' . $sel . '>' . $lang_block['bellow_nav'] . '</option>';
		$sel = $block_config['block_body'] == 'block_body' ? 'selected' : '';
		$html .= '<option value="block_body" ' . $sel . '>' . $lang_block['block_body'] . '</option>';
		$html .= '</select></td>';
		$html .= '</tr>';
		return $html;
	}

	function nv_block_config_news_groups_submit( $module, $lang_block )
	{
		global $nv_Request;
		$return = array();
		$return['error'] = array();
		$return['config'] = array();
		$return['config']['blockid'] = $nv_Request->get_int( 'config_blockid', 'post', 0 );
		$return['config']['numrow'] = $nv_Request->get_int( 'config_numrow', 'post', 0 );
		$return['config']['style'] = $nv_Request->get_string( 'config_style', 'post', '' );
		return $return;
	}

	function nv_block_news_groups( $block_config )
	{
		global $module_array_cat, $module_info, $site_mods, $module_config, $global_config, $db;
		$module = 'news';
		$show_no_image = $module_config[$module]['show_no_image'];
		$blockwidth = $module_config[$module]['blockwidth'];

		$db->sqlreset()
			->select( 't1.id, t1.catid, t1.title, t1.alias, t1.homeimgfile, t1.homeimgthumb,t1.hometext,t1.publtime' )
			->from( NV_PREFIXLANG . '_' . $site_mods[$module]['module_data'] . '_rows t1' )
			->join( 'INNER JOIN ' . NV_PREFIXLANG . '_' . $site_mods[$module]['module_data'] . '_block t2 ON t1.id = t2.id' )
			->where( 't2.bid= ' . $block_config['blockid'] . ' AND t1.status= 1' )
			->order( 't2.weight DESC' )
			->limit( $block_config['numrow'] );
		$list = nv_db_cache( $db->sql(), '', $module );

		if( ! empty( $list ) )
		{
			if( $block_config['style'] == 'bellow_nav' )
			{
				$block_file = 'block_groups_bellow_nav.tpl';
			}
			else
			{
				$block_file = 'block_groups_body.tpl';
			}
			
			if( file_exists( NV_ROOTDIR . '/themes/' . $module_info['template'] . '/blocks/' . $block_file ) )
			{
				$block_theme = $module_info['template'];
			}
			else
			{
				$block_theme = 'default';
			}

			$xtpl = new XTemplate( $block_file, NV_ROOTDIR . '/themes/' . $block_theme . '/blocks' );
			$xtpl->assign( 'BLOCK_CONFIG', $block_config );
			
			foreach( $list as $l )
			{
				$l['link'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module . '&amp;' . NV_OP_VARIABLE . '=' . $module_array_cat[$l['catid']]['alias'] . '/' . $l['alias'] . '-' . $l['id'] . $global_config['rewrite_exturl'];
				if( $l['homeimgthumb'] == 1 )
				{
					$l['thumb'] = NV_BASE_SITEURL . NV_FILES_DIR . '/' . $module . '/' . $l['homeimgfile'];
				}
				elseif( $l['homeimgthumb'] == 2 )
				{
					$l['thumb'] = NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $module . '/' . $l['homeimgfile'];
				}
				elseif( $l['homeimgthumb'] == 3 )
				{
					$l['thumb'] = $l['homeimgfile'];
				}
				elseif( ! empty( $show_no_image ) )
				{
					$l['thumb'] = NV_BASE_SITEURL . $show_no_image;
				}
				else
				{
					$l['thumb'] = '';
				}

				$l['blockwidth'] = $blockwidth;
				
				if( $block_config['style'] == 'block_body' )
				{
					$l['hometext'] = nv_clean60( $l['hometext'], 80 );
					$l['title0'] = nv_clean60( $l['title'], 35 );
				}

				$xtpl->assign( 'ROW', $l );
				if( ! empty( $l['thumb'] ) ) $xtpl->parse( 'main.loop.img' );
				$xtpl->parse( 'main.loop' );
			}

			$xtpl->assign( 'BLOCK_NAME', $block_config['title'] );

			$xtpl->parse( 'main' );
			return $xtpl->text( 'main' );
		}
	}
}
if( defined( 'NV_SYSTEM' ) )
{
	global $site_mods, $module_name, $global_array_cat, $module_array_cat;
	
	$module = 'news';
	
	if( isset( $site_mods[$module] ) )
	{
		if( $module == $module_name )
		{
			$module_array_cat = $global_array_cat;
			unset( $module_array_cat[0] );
		}
		else
		{
			$module_array_cat = array();
			$sql = 'SELECT catid, parentid, title, alias, viewcat, subcatid, numlinks, description, inhome, keywords, groups_view FROM ' . NV_PREFIXLANG . '_' . $site_mods[$module]['module_data'] . '_cat ORDER BY sort ASC';
			$list = nv_db_cache( $sql, 'catid', $module );
			foreach( $list as $l )
			{
				$module_array_cat[$l['catid']] = $l;
				$module_array_cat[$l['catid']]['link'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module . '&amp;' . NV_OP_VARIABLE . '=' . $l['alias'];
			}
		}
		$content = nv_block_news_groups( $block_config );
	}
}
