<?php
/**
* @package phpBB Extension - warcraft 3d item bbcode
* @copyright (c) 2016 talonos - http://pretereo-stormrage.co.uk
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*/

namespace talonos\wow3ditem\migrations;

class v0_1_1_rc1 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\talonos\wow3ditem\migrations\update_table');
	}

	public function update_data()
	{
		return array(
		array('custom', array(array($this, 'install_bbcode_for_3dbattlepet'))),
		);
	}
	public function revert_data()
	{
		return array(
			array('custom', array(array(&$this, 'remove_bbcode'))),
		);
	}

	public function install_bbcode_for_3dbattlepet()
	{
		if (!class_exists('acp_bbcodes'))
		{
		include($this->phpbb_root_path . 'includes/acp/acp_bbcodes.' . $this->php_ext);
		}
		$bbcode_tool = new \acp_bbcodes();
		$bbcode_name = '3dbattlepet';
		$bbcode_array = array(
		'bbcode_match'		=> '[3dbattlepet]{NUMBER}[/3dbattlepet]',
		'bbcode_tpl'		=> '
		<script type="text/javascript">
		r(function(){
			$(function(){
				$("#model-{NUMBER}").spritespin({
					width		: 280,
					height		: 280,
					frames		: 24,
					image		: "http://media.blizzard.com/wow/renders/npcs/rotate/creature{NUMBER}.jpg",
					animate		: false,
					loop		: true,
				});
			});
		});
		
		function r(f){/in/.test(document.readyState)?setTimeout("r("+f+")",9):f()}
		</script>
		<div>
			<div align="center" width="280px" height="280px" >
					<div id="item-{NUMBER}"><a href="http://www.wowhead.com/npc={NUMBER}"></a></div>
				<div class="model-viewer" id="model-{NUMBER}">
				</div>
			</div>
		</div>
		',
		'bbcode_helpline'	=> '[3dbattlepet]ITEM ID NUMBER[/3dbattlepet]',
		'display_on_posting'	=> 1
		);

		$data = $bbcode_tool->build_regexp($bbcode_array['bbcode_match'], $bbcode_array['bbcode_tpl'], $bbcode_array['bbcode_helpline']);

		$bbcode_array += array(
			'bbcode_tag'			=> $data['bbcode_tag'],
			'first_pass_match'	=> $data['first_pass_match'],
			'first_pass_replace'	=> $data['first_pass_replace'],
			'second_pass_match'	=> $data['second_pass_match'],
			'second_pass_replace' => $data['second_pass_replace']
		);

		$sql = 'SELECT bbcode_id
			FROM ' . $this->table_prefix . "bbcodes
			WHERE LOWER(bbcode_tag) = '" . strtolower($bbcode_name) . "'
			OR LOWER(bbcode_tag) = '" . strtolower($bbcode_array['bbcode_tag']) . "'";
		$result = $this->db->sql_query($sql);
		$row_exists = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row_exists)
		{
		$bbcode_id = $row_exists['bbcode_id'];

		$sql = 'UPDATE ' . $this->table_prefix . 'bbcodes
			SET ' . $this->db->sql_build_array('UPDATE', $bbcode_array) . '
			WHERE bbcode_id = ' . $bbcode_id;
		$this->db->sql_query($sql);
		}
		else
		{
		$sql = 'SELECT MAX(bbcode_id) AS max_bbcode_id
			FROM ' . $this->table_prefix . 'bbcodes';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row)
		{
		$bbcode_id = $row['max_bbcode_id'] + 1;
			if ($bbcode_id <= NUM_CORE_BBCODES)
			{
			$bbcode_id = NUM_CORE_BBCODES + 1;
			}
		}
		else
		{
			$bbcode_id = NUM_CORE_BBCODES + 1;
		}

		if ($bbcode_id <= BBCODE_LIMIT)
		{
			$bbcode_array['bbcode_id'] = (int) $bbcode_id;
			$this->db->sql_query('INSERT INTO ' . $this->table_prefix . 'bbcodes ' . $this->db->sql_build_array('INSERT', $bbcode_array));
		}
		}
	}

	public function remove_bbcode()
	{
		$sql = 'DELETE FROM ' . BBCODES_TABLE . ' WHERE bbcode_tag = \'3dbattlepet\'';
		$this->db->sql_query($sql);
	}
}