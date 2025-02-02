<?php
/* Copyright (C) NAVER <http://www.navercorp.com> */
/**
 * Admin model class of the file module
 * @author NAVER (developers@xpressengine.com)
 */
class fileAdminModel extends file
{
	/**
	 * Initialization
	 * @return void
	 */
	public function init()
	{
	}

	/**
	 * Get all the attachments in order by time descending (for administrators)
	 *
	 * <pre>
	 * Search options:
	 * - s_module_srl:          int[] or int, search module_srl
	 * - exclude_module_srl:    int[] or int, exclude module_srl
	 * - isvalid:               Y or N
	 * - direct_download:       Y or N
	 * - s_filename:            string, like operation
	 * - s_filesize_more:       int, more operation, byte unit
	 * - s_filesize_mega_more:  int, more operation, mega unit
	 * - s_filesize_less:       int, less operation, byte unit
	 * - s_filesize_mega_less:  int, less operation, mega unit
	 * - s_download_count:      int, more operation
	 * - s_regdate:             string(YYYYMMDDHHMMSS), like prefix operation(STRING%)
	 * - s_ipaddress:           string, like prefix operation
	 * - s_user_id:             string
	 * - s_user_name:           string
	 * - s_nick_name:           string
	 * - sort_index:            string. default: files.file_srl
	 * - page :                 int
	 * - list_count:            int. default: 20
	 * - page_count:            int. default: 10
	 *
	 * Result data:
	 * - file_srl
	 * - upload_target_srl
	 * - upload_target_type
	 * - sid
	 * - module_srl
	 * - member_srl
	 * - download_count
	 * - direct_download
	 * - source_filename
	 * - uploaded_filename
	 * - file_size
	 * - comment
	 * - isvaild
	 * - regdate
	 * - ipaddress
	 * 
	 * </pre>
	 *
	 * @param object $obj Search options
	 * @param array $columnList Column list to get from DB
	 * @return Object Object contains query result
	 */
	public function getFileList($obj, $columnList = array())
	{
		$args = new stdClass();
		$this->_makeSearchParam($obj, $args);

		// Set valid/invalid state
		if(isset($obj->isvalid) && in_array($obj->isvalid, ['Y', 'N']))
		{
			$args->isvalid = $obj->isvalid;
		}
		
		// Set multimedia/common file
		if(isset($obj->direct_download) && in_array($obj->direct_download, ['Y', 'N']))
		{
			$args->direct_download = $obj->direct_download;
		}
		
		// Set variables
		$args->sort_index = $obj->sort_index ?? null;
		$args->order_type = $obj->order_type ?? 'desc';
		$args->page = isset($obj->page) ? ($obj->page ? $obj->page : 1) : 1;
		$args->list_count = isset($obj->list_count) ? ($obj->list_count? $obj->list_count : 20) : 20;
		$args->page_count = isset($obj->page_count) ? ($obj->page_count? $obj->page_count : 10) : 10;
		$args->s_module_srl = $obj->module_srl ?? null;
		$args->exclude_module_srl = $obj->exclude_module_srl ?? null;
		if(toBool($obj->exclude_secret ?? null))
		{
			$args->document_status = array('PUBLIC');
			$args->comment_is_secret = array('N');
			$output = executeQueryArray('file.getFileListByTargetStatus', $args, $columnList);
		}
		else
		{
			$output = executeQueryArray('file.getFileList', $args, $columnList);
		}
		
		// Return if no result or an error occurs
		if(!$output->toBool() || !count($output->data))
		{
			return $output;
		}

		foreach($output->data as $key => $file)
		{
			if(isset($_SESSION['file_management'][$file->file_srl]) && $_SESSION['file_management'][$file->file_srl])
			{
				$file->isCarted = true;
			}
			else
			{
				$file->isCarted = false;
			}
			$file->download_url = FileModel::getDownloadUrl($file->file_srl, $file->sid, $file->module_srl);
			$output->data[$key] = $file;
		}

		return $output;
	}

	/**
	 * Return number of attachments which belongs to a specific document
	 *
	 * <pre>
	 * Result data:
	 * +---------+-------+
	 * | isvalid | count |
	 * +---------+-------+
	 * | Y       | 00    |
	 * +---------+-------+
	 * | N       | 00    |
	 * +---------+-------+
	 * </pre>
	 *
	 * @param object $obj Search options (not used...)
	 * @return array
	 */
	public function getFilesCountByGroupValid($obj = '')
	{
		//$this->_makeSearchParam($obj, $args);

		$output = executeQueryArray('file.getFilesCountByGroupValid', $args);
		return $output->data;
	}

	/**
	 * Return number of attachments which belongs to a specific date
	 *
	 * @param string $date Date string
	 * @return int
	 */
	public function getFilesCountByDate($date = '')
	{
		$args = new stdClass();
		if($date)
		{
			$args->regDate = date('Ymd', strtotime($date));
		}

		$output = executeQuery('file.getFilesCount', $args);
		if(!$output->toBool())
		{
			return 0;
		}

		return $output->data->count;
	}

	/**
	 * Make search parameters from object(private)
	 *
	 * @param object $obj Original searach options
	 * @param object $args Result searach options
	 * @return void
	 */
	protected function _makeSearchParam(&$obj, &$args)
	{
		// Search options
		$search_target = isset($obj->search_target)? ($obj->search_target? $obj->search_target : trim(Context::get('search_target'))) : trim(Context::get('search_target'));
		$search_keyword = isset($obj->search_keyword)? ($obj->search_keyword? $obj->search_keyword : trim(Context::get('search_keyword'))) : trim(Context::get('search_keyword'));

		if($search_target && $search_keyword)
		{
			switch($search_target)
			{
				case 'filename' :
					$args->s_filename = $search_keyword;
					break;
				case 'filesize_more' :
					$args->s_filesize_more = (int)$search_keyword;
					break;
				case 'filesize_mega_more' :
					$args->s_filesize_more = (int)$search_keyword * 1024 * 1024;
					break;
				case 'filesize_less' :
					$args->s_filesize_less = (int)$search_keyword;
					break;
				case 'filesize_mega_less' :
					$args->s_filesize_less = (int)$search_keyword * 1024 * 1024;
					break;
				case 'download_count' :
					$args->s_download_count = (int)$search_keyword;
					break;
				case 'download_count_less' :
					$args->s_download_count_less = (int)$search_keyword;
					break;
				case 'regdate' :
					$args->s_regdate = $search_keyword;
					break;
				case 'ipaddress' :
					$args->s_ipaddress = $search_keyword;
					break;
				case 'user_id' :
					$args->s_user_id = $search_keyword;
					break;
				case 'user_name' :
					$args->s_user_name = $search_keyword;
					break;
				case 'nick_name' :
					$args->s_nick_name = $search_keyword;
					break;
				case 'isvalid' :
					$args->isvalid = $search_keyword;
					break;
			}
		}
	}
}
/* End of file file.admin.model.php */
/* Location: ./modules/file/file.admin.model.php */
