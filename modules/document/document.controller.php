<?php
/* Copyright (C) NAVER <http://www.navercorp.com> */
/**
 * documentController class
 * document the module's controller class
 *
 * @author NAVER (developers@xpressengine.com)
 * @package /modules/document
 * @version 0.1
 */
class DocumentController extends Document
{
	/**
	 * Initialization
	 * @return void
	 */
	function init()
	{
	}

	/**
	 * Action to handle vote-up of the post (Up)
	 * @return Object
	 */
	function procDocumentVoteUp()
	{
		$document_srl = Context::get('target_srl');
		if(!$document_srl)
		{
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}

		// Check target document.
		$oDocument = DocumentModel::getDocument($document_srl, false, false);
		if(!$oDocument->isExists())
		{
			throw new Rhymix\Framework\Exceptions\TargetNotFound;
		}
		if(!$oDocument->isAccessible(true))
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}

		// Check if voting is enabled.
		$document_config = ModuleModel::getModulePartConfig('document', $oDocument->get('module_srl'));
		if($document_config->use_vote_up === 'N')
		{
			throw new Rhymix\Framework\Exceptions\FeatureDisabled;
		}
		if(!Context::get('is_logged'))
		{
			if (isset($document_config->allow_vote_non_member))
			{
				if ($document_config->allow_vote_non_member !== 'Y')
				{
					throw new Rhymix\Framework\Exceptions\MustLogin;
				}
			}
			else
			{
				$module_info = $this->module_info ?: ModuleModel::getModuleInfoByModuleSrl($oDocument->get('module_srl'));
				if (($module_info->non_login_vote ?? 'N') !== 'Y')
				{
					throw new Rhymix\Framework\Exceptions\MustLogin;
				}
			}
		}

		$point = 1;
		$allow_same_ip = ($document_config->allow_vote_from_same_ip ?? 'N') === 'Y';
		$output = $this->updateVotedCount($document_srl, $point, $allow_same_ip);
		if(!$output->toBool())
		{
			return $output;
		}
		$this->add('voted_count', $output->get('voted_count'));
		return $output;
	}

	function procDocumentVoteUpCancel()
	{
		$document_srl = Context::get('target_srl');
		if(!$document_srl)
		{
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}

		// Check target document.
		$oDocument = DocumentModel::getDocument($document_srl, false, false);
		if(!$oDocument->isExists())
		{
			throw new Rhymix\Framework\Exceptions\TargetNotFound;
		}
		if(!$oDocument->isAccessible(true))
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}
		if($oDocument->get('voted_count') <= 0)
		{
			throw new Rhymix\Framework\Exception('failed_voted_canceled');
		}

		// Check if voting and canceling are enabled.
		$document_config = ModuleModel::getModulePartConfig('document', $oDocument->get('module_srl'));
		$module_info = $this->module_info ?: ModuleModel::getModuleInfoByModuleSrl($oDocument->get('module_srl'));
		if (isset($document_config->allow_vote_cancel))
		{
			if ($document_config->allow_vote_cancel !== 'Y')
			{
				throw new Rhymix\Framework\Exceptions\FeatureDisabled;
			}
		}
		else
		{
			if (($module_info->cancel_vote ?? 'N') !== 'Y')
			{
				throw new Rhymix\Framework\Exceptions\FeatureDisabled;
			}
		}
		if(!Context::get('is_logged'))
		{
			if (isset($document_config->allow_vote_non_member))
			{
				if ($document_config->allow_vote_non_member !== 'Y')
				{
					throw new Rhymix\Framework\Exceptions\MustLogin;
				}
			}
			else
			{
				if (($module_info->non_login_vote ?? 'N') !== 'Y')
				{
					throw new Rhymix\Framework\Exceptions\MustLogin;
				}
			}
		}

		$point = 1;
		$output = $this->updateVotedCountCancel($document_srl, $oDocument, $point);
		if(!$output->toBool())
		{
			return $output;
		}
		$this->add('voted_count', $output->get('voted_count'));
		return $output;
	}

	/**
	 * Action to handle vote-up of the post (Down)
	 * @return Object
	 */
	function procDocumentVoteDown()
	{
		$document_srl = Context::get('target_srl');
		if(!$document_srl)
		{
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}

		// Check target document.
		$oDocument = DocumentModel::getDocument($document_srl, false, false);
		if(!$oDocument->isExists())
		{
			throw new Rhymix\Framework\Exceptions\TargetNotFound;
		}
		if(!$oDocument->isAccessible(true))
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}

		// Check if voting is enabled.
		$document_config = ModuleModel::getModulePartConfig('document', $oDocument->get('module_srl'));
		if($document_config->use_vote_down === 'N')
		{
			throw new Rhymix\Framework\Exceptions\FeatureDisabled;
		}
		if(!Context::get('is_logged'))
		{
			if (isset($document_config->allow_vote_non_member))
			{
				if ($document_config->allow_vote_non_member !== 'Y')
				{
					throw new Rhymix\Framework\Exceptions\MustLogin;
				}
			}
			else
			{
				$module_info = $this->module_info ?: ModuleModel::getModuleInfoByModuleSrl($oDocument->get('module_srl'));
				if (($module_info->non_login_vote ?? 'N') !== 'Y')
				{
					throw new Rhymix\Framework\Exceptions\MustLogin;
				}
			}
		}

		$point = -1;
		$allow_same_ip = ($document_config->allow_vote_from_same_ip ?? 'N') === 'Y';
		$output = $this->updateVotedCount($document_srl, $point, $allow_same_ip);
		if(!$output->toBool())
		{
			return $output;
		}
		$this->add('blamed_count', $output->get('blamed_count'));
		return $output;
	}

	function procDocumentVoteDownCancel()
	{
		$document_srl = Context::get('target_srl');
		if(!$document_srl)
		{
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}

		// Check target document.
		$oDocument = DocumentModel::getDocument($document_srl, false, false);
		if(!$oDocument->isExists())
		{
			throw new Rhymix\Framework\Exceptions\TargetNotFound;
		}
		if(!$oDocument->isAccessible(true))
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}
		if($oDocument->get('blamed_count') >= 0)
		{
			throw new Rhymix\Framework\Exception('failed_blamed_canceled');
		}

		// Check if voting and canceling are enabled.
		$document_config = ModuleModel::getModulePartConfig('document', $oDocument->get('module_srl'));
		$module_info = $this->module_info ?: ModuleModel::getModuleInfoByModuleSrl($oDocument->get('module_srl'));
		if (isset($document_config->allow_vote_cancel))
		{
			if ($document_config->allow_vote_cancel !== 'Y')
			{
				throw new Rhymix\Framework\Exceptions\FeatureDisabled;
			}
		}
		else
		{
			if (($module_info->cancel_vote ?? 'N') !== 'Y')
			{
				throw new Rhymix\Framework\Exceptions\FeatureDisabled;
			}
		}
		if(!Context::get('is_logged'))
		{
			if (isset($document_config->allow_vote_non_member))
			{
				if ($document_config->allow_vote_non_member !== 'Y')
				{
					throw new Rhymix\Framework\Exceptions\MustLogin;
				}
			}
			else
			{
				if (($module_info->non_login_vote ?? 'N') !== 'Y')
				{
					throw new Rhymix\Framework\Exceptions\MustLogin;
				}
			}
		}

		$point = -1;
		$output = $this->updateVotedCountCancel($document_srl, $oDocument, $point);
		if(!$output->toBool())
		{
			return $output;
		}
		$this->add('blamed_count', $output->get('blamed_count'));
		return $output;
	}

	/**
	 * Update Document Voted Cancel
	 * @param int $document_srl
	 * @param DocumentItem $oDocument
	 * @param int $point
	 * @return object
	 */
	function updateVotedCountCancel($document_srl, $oDocument, $point)
	{
		// Guests can only cancel votes that are registered in the current session.
		if(!$this->user->member_srl && empty($_SESSION['voted_document'][$document_srl]))
		{
			return new BaseObject(-1, $point > 0 ? 'failed_voted_canceled' : 'failed_blamed_canceled');
		}

		// Check if the current user has voted previously.
		$args = new stdClass;
		$args->document_srl = $document_srl;
		if($this->user->member_srl)
		{
			$args->member_srl = $this->user->member_srl;
		}
		else
		{
			$args->member_srl = 0;
			$args->ipaddress = \RX_CLIENT_IP;
		}
		$output = executeQuery('document.getDocumentVotedLogInfo', $args);

		if(!$output->data->count)
		{
			return new BaseObject(-1, $point > 0 ? 'failed_voted_canceled' : 'failed_blamed_canceled');
		}

		$point = $output->data->point;

		// Call a trigger (before)
		$trigger_obj = new stdClass;
		$trigger_obj->member_srl = $oDocument->get('member_srl');
		$trigger_obj->module_srl = $oDocument->get('module_srl');
		$trigger_obj->document_srl = $oDocument->get('document_srl');
		$trigger_obj->update_target = ($point < 0) ? 'blamed_count' : 'voted_count';
		$trigger_obj->point = $point;
		$trigger_obj->before_point = ($point < 0) ? $oDocument->get('blamed_count') : $oDocument->get('voted_count');
		$trigger_obj->after_point = $trigger_obj->before_point - $point;
		$trigger_obj->cancel = true;
		$trigger_output = ModuleHandler::triggerCall('document.updateVotedCountCancel', 'before', $trigger_obj);
		if(!$trigger_output->toBool())
		{
			return $trigger_output;
		}

		// begin transaction
		$oDB = DB::getInstance();
		$oDB->begin();

		if($point != 0)
		{
			$args = new stdClass();
			$d_args = new stdClass();
			$args->document_srl = $d_args->document_srl = $document_srl;
			$d_args->member_srl = $this->user->member_srl;
			if ($trigger_obj->update_target === 'voted_count')
			{
				$args->voted_count = $trigger_obj->after_point;
				$output = executeQuery('document.updateVotedCount', $args);
			}
			else
			{
				$args->blamed_count = $trigger_obj->after_point;
				$output = executeQuery('document.updateBlamedCount', $args);
			}
			$d_output = executeQuery('document.deleteDocumentVotedLog', $d_args);
			if(!$d_output->toBool())
			{
				$oDB->rollback();
				return $d_output;
			}
		}
		// session reset
		unset($_SESSION['voted_document'][$document_srl]);

		// Call a trigger (after)
		ModuleHandler::triggerCall('document.updateVotedCountCancel', 'after', $trigger_obj);

		$oDB->commit();

		// Return result
		$output = new BaseObject();
		if($trigger_obj->update_target === 'voted_count')
		{
			$output->setMessage('success_voted_canceled');
			$output->add('voted_count', $trigger_obj->after_point);
		}
		else
		{
			$output->setMessage('success_blamed_canceled');
			$output->add('blamed_count', $trigger_obj->after_point);
		}
		return $output;
	}

	/**
	 * Action called when the post is reported by other member
	 * @return void|Object
	 */
	function procDocumentDeclare()
	{
		if(!Context::get('is_logged'))
		{
			throw new Rhymix\Framework\Exceptions\MustLogin;
		}

		$document_srl = Context::get('target_srl');
		if(!$document_srl)
		{
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}
		$oDocument = DocumentModel::getDocument($document_srl, false, false);
		if(!$oDocument->isExists())
		{
			throw new Rhymix\Framework\Exceptions\TargetNotFound;
		}
		if(!$oDocument->isAccessible(true))
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}

		// if an user select message from options, message would be the option.
		$message_option = strval(Context::get('message_option'));
		$improper_document_reasons = lang('improper_document_reasons');
		$declare_message = ($message_option !== 'others' && isset($improper_document_reasons[$message_option])) ? $improper_document_reasons[$message_option] : trim(Context::get('declare_message'));

		// if there is return url, set that.
		if(Context::get('success_return_url'))
		{
			$this->setRedirectUrl(Context::get('success_return_url'));
		}

		return $this->declaredDocument($document_srl, $declare_message);
	}

	/**
	 * 신고를 취소하는 액션
	 * @return BaseObject|object
	 * @throws \Rhymix\Framework\Exceptions\InvalidRequest
	 * @throws \Rhymix\Framework\Exceptions\MustLogin
	 */
	function procDocumentDeclareCancel()
	{
		if(!Context::get('is_logged'))
		{
			throw new Rhymix\Framework\Exceptions\MustLogin;
		}

		// Check if the document exists and is accessible to the current user.
		$document_srl = Context::get('target_srl');
		if(!$document_srl)
		{
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}
		$oDocument = DocumentModel::getDocument($document_srl, false, false);
		if(!$oDocument->isExists())
		{
			throw new Rhymix\Framework\Exceptions\TargetNotFound;
		}
		if(!$oDocument->isAccessible(true))
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}

		// Check if canceling is allowed.
		$document_config = ModuleModel::getModulePartConfig('document', $oDocument->get('module_srl'));
		if (isset($document_config->allow_declare_cancel))
		{
			if ($document_config->allow_declare_cancel !== 'Y')
			{
				throw new Rhymix\Framework\Exception('failed_declared_cancel');
			}
		}
		else
		{
			$module_info = ModuleModel::getModuleInfoByModuleSrl($oDocument->get('module_srl'));
			if (($module_info->cancel_vote ?? 'N') !== 'Y')
			{
				throw new Rhymix\Framework\Exception('failed_declared_cancel');
			}
		}

		if(Context::get('success_return_url'))
		{
			$this->setRedirectUrl(Context::get('success_return_url'));
		}

		return $this->declaredDocumentCancel($document_srl);
	}

	/**
	 * Delete temporarily saved document
	 */
	public function procDocumentDeleteTempSaved()
	{
		$document_srl = Context::get('document_srl');
		if ($document_srl <= 0)
		{
		throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}

		$oDocument = DocumentModel::getDocument($document_srl);
		if (!$oDocument || !$oDocument->isExists())
		{
			throw new Rhymix\Framework\Exceptions\TargetNotFound;
		}
		if ($oDocument->get('member_srl') !== $this->user->member_srl || $oDocument->getStatus() !== 'TEMP' || !$oDocument->isGranted())
		{
			throw new Rhymix\Framework\Exceptions\TargetNotFound;
		}

		$output = $this->deleteDocument($document_srl);
		if ($output instanceof BaseObject && !$output->toBool())
		{
			return $output;
		}
	}

	/**
	 * insert alias
	 * @param int $module_srl
	 * @param int $document_srl
	 * @param string $alias_title
	 * @return object
	 */
	function insertAlias($module_srl, $document_srl, $alias_title)
	{
		$args = new stdClass;
		$args->alias_srl = getNextSequence();
		$args->module_srl = $module_srl;
		$args->document_srl = $document_srl;
		$args->alias_title = urldecode($alias_title);
		$query = "document.insertAlias";
		$output = executeQuery($query, $args);
		return $output;
	}

	/**
	 * Delete alias when module deleted
	 * @param int $module_srl
	 * @return void
	 */
	function deleteDocumentAliasByModule($module_srl)
	{
		$args = new stdClass();
		$args->module_srl = $module_srl;
		executeQuery("document.deleteAlias", $args);
	}

	/**
	 * Delete alias when document deleted
	 * @param int $document_srl
	 * @return void
	 */
	function deleteDocumentAliasByDocument($document_srl)
	{
		$args = new stdClass();
		$args->document_srl = $document_srl;
		executeQuery("document.deleteAlias", $args);
	}

	/**
	 * Delete document history
	 * @param int $history_srl
	 * @param int $document_srl
	 * @param int $module_srl
	 * @return void
	 */
	function deleteDocumentHistory($history_srl, $document_srl, $module_srl)
	{
		$args = new stdClass();
		$args->history_srl = $history_srl;
		$args->module_srl = $module_srl;
		$args->document_srl = $document_srl;
		if(!$args->history_srl && !$args->module_srl && !$args->document_srl) return;
		executeQuery("document.deleteHistory", $args);
	}

	/**
	 * A trigger to delete all posts together when the module is deleted
	 * @param object $obj
	 * @return Object
	 */
	function triggerDeleteModuleDocuments(&$obj)
	{
		$module_srl = $obj->module_srl;
		if(!$module_srl) return;
		// Delete the document
		$oDocumentAdminController = DocumentAdminController::getInstance();
		$output = $oDocumentAdminController->deleteModuleDocument($module_srl);
		if(!$output->toBool()) return $output;
		// Delete the category
		$output = $this->deleteModuleCategory($module_srl);
		if(!$output->toBool()) return $output;
		// Delete extra key and variable, because module deleted
		$this->deleteDocumentExtraKeys($module_srl);

		// remove aliases
		$this->deleteDocumentAliasByModule($module_srl);

		// remove histories
		$this->deleteDocumentHistory(null, null, $module_srl);
	}

	/**
	 * Grant a permisstion of the document
	 * Available in the current connection with session value
	 * @param int $document_srl
	 * @return void
	 */
	function addGrant($document_srl)
	{
		$oDocument = DocumentModel::getDocument($document_srl);
		if ($oDocument->isExists())
		{
			$oDocument->setGrant();
		}
	}

	/**
	 * Insert the document
	 * @param object $obj
	 * @param bool $manual_inserted
	 * @param bool $isRestore
	 * @return object
	 */
	function insertDocument($obj, $manual_inserted = false, $isRestore = false, $isLatest = true)
	{
		if (!$manual_inserted && !checkCSRF())
		{
			return new BaseObject(-1, 'msg_security_violation');
		}

		// Comment status
		if (isset($obj->comment_status) && $obj->comment_status)
		{
			$obj->commentStatus = $obj->comment_status;
		}
		if (!isset($obj->commentStatus) || !$obj->commentStatus)
		{
			$obj->commentStatus = 'DENY';
		}
		if ($obj->commentStatus === 'DENY')
		{
			$this->_checkCommentStatusForOldVersion($obj);
		}

		if (!isset($obj->allow_trackback) || $obj->allow_trackback !== 'Y')
		{
			$obj->allow_trackback = 'N';
		}
		if (!isset($obj->notify_message) || $obj->notify_message !== 'Y')
		{
			$obj->notify_message = 'N';
		}
		if (!isset($obj->email_address))
		{
			$obj->email_address = '';
		}

		if (!empty($obj->homepage))
		{
			$obj->homepage = escape($obj->homepage);
			if(!preg_match('/^[a-z]+:\/\//i',$obj->homepage))
			{
				$obj->homepage = 'http://'.$obj->homepage;
			}
		}

		if (!$isRestore)
		{
			$obj->ipaddress = \RX_CLIENT_IP;
		}
		$obj->isRestore = $isRestore ? true : false;

		// Sanitize variables
		$obj->document_srl = intval($obj->document_srl ?? 0);
		$obj->category_srl = intval($obj->category_srl ?? 0);
		$obj->module_srl = intval($obj->module_srl ?? 0);

		// Default Status
		if (isset($obj->status) && $obj->status)
		{
			if (!in_array($obj->status, $this->getStatusList()))
			{
				$obj->status = $this->getDefaultStatus();
			}
		}
		else
		{
			$this->_checkDocumentStatusForOldVersion($obj);
		}

		// Check publish status
		$is_publish = $obj->status !== 'TEMP';

		// Dates can only be manipulated by administrators.
		$grant = Context::get('grant');
		if (!$grant->manager)
		{
			unset($obj->regdate);
			unset($obj->last_update);
			unset($obj->last_updater);
		}

		// Serialize the $extra_vars, but avoid duplicate serialization.
		if (!isset($obj->extra_vars))
		{
			$obj->extra_vars = new stdClass;
		}
		if (!is_string($obj->extra_vars))
		{
			$obj->extra_vars = serialize($obj->extra_vars);
		}

		// Remove the columns for automatic saving
		unset($obj->_saved_doc_srl);
		unset($obj->_saved_doc_title);
		unset($obj->_saved_doc_content);
		unset($obj->_saved_doc_message);

		// Add the current user's info, unless it is a guest post
		$logged_info = Context::get('logged_info');
		if($logged_info->member_srl && !$manual_inserted && !$isRestore)
		{
			$obj->member_srl = $logged_info->member_srl;
			$obj->user_id = htmlspecialchars_decode($logged_info->user_id);
			$obj->user_name = htmlspecialchars_decode($logged_info->user_name);
			$obj->nick_name = htmlspecialchars_decode($logged_info->nick_name);
			$obj->email_address = $logged_info->email_address;
			$obj->homepage = $logged_info->homepage;
		}
		if(!$logged_info->member_srl && !$manual_inserted && !$isRestore)
		{
			unset($obj->member_srl);
			unset($obj->user_id);
		}

		$obj->uploaded_count = FileModel::getFilesCount($obj->document_srl, 'doc');

		// Call a trigger (before)
		$output = ModuleHandler::triggerCall('document.insertDocument', 'before', $obj);
		if(!$output->toBool())
		{
			return $output;
		}

		// Call publish trigger (before)
		if ($is_publish)
		{
			$output = ModuleHandler::triggerCall('document.publishDocument', 'before', $obj);
			if(!$output->toBool())
			{
				return $output;
			}
		}

		// Register it if no given document_srl exists
		if(!$obj->document_srl)
		{
			$obj->document_srl = getNextSequence();
		}
		elseif(!$manual_inserted && !$isRestore && !checkUserSequence($obj->document_srl))
		{
			return new BaseObject(-1, 'msg_not_permitted');
		}

		// Set to 0 if the category_srl doesn't exist
		if($obj->category_srl)
		{
			$category_list = DocumentModel::getCategoryList($obj->module_srl);
			if (count($category_list) > 0)
			{
				if (isset($category_list[$obj->category_srl]))
				{
					if (!$category_list[$obj->category_srl]->grant)
					{
						return new BaseObject(-1, 'msg_not_permitted');
					}
				}
				else
				{
					$obj->category_srl = 0;
				}
			}
		}

		// Set the read counts and update order.
		if (!isset($obj->readed_count))
		{
			$obj->readed_count = 0;
		}
		if ($isLatest)
		{
			$obj->update_order = $obj->list_order = $obj->document_srl * -1;
		}
		else
		{
			$obj->update_order = $obj->list_order;
		}

		// Check the status of password hash for manually inserting. Apply hashing for otherwise.
		if(!empty($obj->password) && !$obj->password_is_hashed)
		{
			$obj->password = \Rhymix\Framework\Password::hashPassword($obj->password, \Rhymix\Framework\Password::getBackwardCompatibleAlgorithm());
		}

		// If the tile is empty, extract string from the contents.
		$obj->title = escape($obj->title ?? '', false);
		if ($obj->title === '')
		{
			$obj->title = escape(cut_str(trim(utf8_normalize_spaces(strip_tags($obj->content))), 20, '...'), false);
		}
		if ($obj->title === '')
		{
			$obj->title = 'Untitled';
		}

		// Remove XE's own tags from the contents.
		$obj->content = preg_replace('!<\!--(Before|After)(Document|Comment)\(([0-9]+),([0-9]+)\)-->!is', '', $obj->content);

		// Return error if content is empty.
		if (!$manual_inserted && is_empty_html_content($obj->content))
		{
			return new BaseObject(-1, 'msg_empty_content');
		}

		// if use editor of nohtml, Remove HTML tags from the contents.
		if(!$manual_inserted || isset($obj->allow_html) || isset($obj->use_html))
		{
			$obj->content = EditorModel::converter($obj, 'document');
		}

		// Remove iframe and script if not a top adminisrator in the session.
		if($logged_info->is_admin != 'Y')
		{
			$obj->content = removeHackTag($obj->content);
		}

		// An error appears if both log-in info and user name don't exist.
		if(!$logged_info->member_srl && !$obj->nick_name) return new BaseObject(-1, 'msg_invalid_request');

		// Fix encoding of non-BMP UTF-8 characters.
		$obj->title = utf8_mbencode($obj->title);
		$obj->content = utf8_mbencode($obj->content);

		$obj->lang_code = Context::getLangType();

		// begin transaction
		$oDB = DB::getInstance();
		$oDB->begin();

		// Insert data into the DB
		$output = executeQuery('document.insertDocument', $obj);
		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}

		// Insert extra variables if the document successfully inserted.
		$extra_vars = array();
		$extra_keys = DocumentModel::getExtraKeys($obj->module_srl);
		if(count($extra_keys))
		{
			foreach($extra_keys as $idx => $extra_item)
			{
				$value = NULL;
				if(isset($obj->{'extra_vars'.$idx}))
				{
					$tmp = $obj->{'extra_vars'.$idx};
					if ($extra_item->type === 'file')
					{
						$value = $tmp;
					}
					elseif (is_array($tmp))
					{
						$value = implode('|@|', $tmp);
					}
					else
					{
						$value = trim($tmp);
					}
				}
				else if(isset($obj->{$extra_item->name}))
				{
					$value = trim($obj->{$extra_item->name});
				}

				// Validate and process the extra value.
				if ($value == NULL && $manual_inserted)
				{
					continue;
				}
				else
				{
					if (!$manual_inserted)
					{
						$ev_output = $extra_item->validate($value);
						if ($ev_output && !$output->toBool())
						{
							$oDB->rollback();
							return $ev_output;
						}
					}

					// Handle extra vars that support file upload.
					if ($extra_item->type === 'file' && is_array($value))
					{
						$ev_output = $extra_item->uploadFile($value, $obj->document_srl, 'doc');
						if (!$ev_output->toBool())
						{
							$oDB->rollback();
							return $ev_output;
						}
						$value = $ev_output->get('file_srl');
					}
				}

				$extra_vars[$extra_item->name] = $value;
				$this->insertDocumentExtraVar($obj->module_srl, $obj->document_srl, $idx, $value, $extra_item->eid);
			}
		}

		// Update the category if the category_srl exists.
		if($obj->category_srl)
		{
			$this->updateCategoryCount($obj->module_srl, $obj->category_srl, '+1');
		}

		// Call a trigger (after)
		if($obj->update_log_setting === 'Y')
		{
			$obj->extra_vars = serialize($extra_vars);
			$update_output = $this->insertDocumentUpdateLog($obj);

			if(!$update_output->toBool())
			{
				$oDB->rollback();
				return $update_output;
			}
		}

		$attachOutput = FileController::getInstance()->setFilesValid($obj->document_srl, 'doc');
		if(!$attachOutput->toBool())
		{
			$oDB->rollback();
			return $attachOutput;
		}

		$obj->updated_file_count = $attachOutput->get('updated_file_count');
		ModuleHandler::triggerCall('document.insertDocument', 'after', $obj);
		if ($is_publish)
		{
			ModuleHandler::triggerCall('document.publishDocument', 'after', $obj);
		}

		// commit
		$oDB->commit();

		// return
		if(!$manual_inserted)
		{
			$this->addGrant($obj->document_srl);
		}
		$output->add('document_srl', $obj->document_srl);
		$output->add('category_srl', $obj->category_srl);

		return $output;
	}

	/**
	 * Update the document
	 * @param object $source_obj
	 * @param object $obj
	 * @param bool $manual_updated
	 * @return object
	 */
	function updateDocument($source_obj, $obj, $manual_updated = FALSE)
	{
		if(!$manual_updated && !checkCSRF())
		{
			return new BaseObject(-1, 'msg_security_violation');
		}

		if(!$source_obj->document_srl || !$obj->document_srl)
		{
			return new BaseObject(-1, 'msg_invalied_request');
		}

		// Sanitize variables
		$obj->document_srl = intval($obj->document_srl);
		$obj->category_srl = intval($obj->category_srl);
		$obj->module_srl = intval($obj->module_srl);

		// Default Status
		if($obj->status)
		{
			if(!in_array($obj->status, $this->getStatusList()))
			{
				$obj->status = $this->getDefaultStatus();
			}

			// Do not update to temp document (point problem)
			if($obj->status == $this->getConfigStatus('temp'))
			{
				$obj->status = $source_obj->get('status');
			}
		}
		else
		{
			$this->_checkDocumentStatusForOldVersion($obj);
		}

		// Check publish status (update from TEMP to non-TEMP)
		$is_publish = ($obj->status !== 'TEMP' && $source_obj->get('status') === 'TEMP');

		// Preserve original author info.
		if ($source_obj->get('member_srl'))
		{
			$obj->member_srl = $source_obj->get('member_srl');
			$obj->user_id = $source_obj->get('user_id');
			$obj->user_name = $source_obj->get('user_name');
			$obj->nick_name = $source_obj->get('nick_name');
			$obj->email_address = $source_obj->get('email_address');
			$obj->homepage = $source_obj->get('homepage');
			$obj->ipaddress = $source_obj->get('ipaddress');
		}
		else
		{
			unset($obj->member_srl);
			unset($obj->user_id);
			unset($obj->user_name);
			$obj->nick_name = $obj->nick_name ?? $source_obj->get('nick_name');
			$obj->email_address = $obj->email_address ?? $source_obj->get('email_address');
			$obj->homepage = $obj->homepage ?? $source_obj->get('homepage');
			$obj->ipaddress = $source_obj->get('ipaddress');
		}

		if(!isset($obj->is_notice)) $obj->is_notice = 'N';
		if(($obj->title_bold ?? 'N') !== 'Y') $obj->title_bold = 'N';
		if(($obj->title_color ?? 'N') === 'N') $obj->title_color = 'N';
		if(($obj->notify_message ?? 'N') !== 'Y') $obj->notify_message = 'N';
		if(($obj->allow_trackback ?? 'N') !== 'Y') $obj->allow_trackback = 'N';
		$obj->uploaded_count = FileModel::getFilesCount($obj->document_srl, 'doc');

		// Call a trigger (before)
		$output = ModuleHandler::triggerCall('document.updateDocument', 'before', $obj);
		if(!$output->toBool())
		{
			return $output;
		}

		// Call publish trigger (before)
		if ($is_publish)
		{
			$output = ModuleHandler::triggerCall('document.publishDocument', 'before', $obj);
			if(!$output->toBool())
			{
				return $output;
			}
		}

		if(!$obj->module_srl) $obj->module_srl = $source_obj->get('module_srl');

		$document_config = ModuleModel::getModulePartConfig('document', $obj->module_srl);
		if(!$document_config)
		{
			$document_config = new stdClass();
		}
		if(!isset($document_config->use_history))
		{
			$document_config->use_history = 'N';
		}

		$logged_info = Context::get('logged_info');

		// List variables
		if($obj->comment_status) $obj->commentStatus = $obj->comment_status;
		if(!$obj->commentStatus) $obj->commentStatus = 'DENY';
		if($obj->commentStatus == 'DENY') $this->_checkCommentStatusForOldVersion($obj);
		if($obj->homepage)
		{
			$obj->homepage = escape($obj->homepage);
			if(!preg_match('/^[a-z]+:\/\//i',$obj->homepage))
			{
				$obj->homepage = 'http://'.$obj->homepage;
			}
		}

		// can modify regdate only manager
		$grant = Context::get('grant');
		if(!$grant->manager && !$manual_updated)
		{
			unset($obj->regdate);
			unset($obj->last_update);
			unset($obj->list_order);
		}

		// Set default values for regdate, list_order, and update_order.
		if ($is_publish)
		{
			$obj->regdate = date('YmdHis');
			$obj->list_order = getNextSequence() * -1;
			$obj->update_order = $obj->list_order;
		}
		else
		{
			$obj->update_order = getNextSequence() * -1;
		}

		// Serialize the $extra_vars
		if (isset($obj->extra_vars) && !is_string($obj->extra_vars))
		{
			$obj->extra_vars = serialize($obj->extra_vars);
		}

		// Remove the columns for automatic saving
		unset($obj->_saved_doc_srl);
		unset($obj->_saved_doc_title);
		unset($obj->_saved_doc_content);
		unset($obj->_saved_doc_message);

		// Set the category_srl to 0 if the changed category is not exsiting.
		if ($source_obj->get('category_srl') != $obj->category_srl)
		{
			$category_list = DocumentModel::getCategoryList($obj->module_srl);
			if (count($category_list) > 0)
			{
				if (isset($category_list[$obj->category_srl]))
				{
					if (!$category_list[$obj->category_srl]->grant)
					{
						return new BaseObject(-1, 'msg_not_permitted');
					}
				}
				else
				{
					$obj->category_srl = 0;
				}
			}
		}

		// Hash the password if it exists
		if (!empty($obj->password))
		{
			$obj->password = \Rhymix\Framework\Password::hashPassword($obj->password, \Rhymix\Framework\Password::getBackwardCompatibleAlgorithm());
		}

		// If the tile is empty, extract string from the contents.
		$obj->title = escape($obj->title, false);
		if ($obj->title === '')
		{
			$obj->title = escape(cut_str(trim(utf8_normalize_spaces(strip_tags($obj->content))), 20, '...'), false);
		}
		if ($obj->title === '')
		{
			$obj->title = 'Untitled';
		}

		// Remove XE's own tags from the contents.
		$obj->content = preg_replace('!<\!--(Before|After)(Document|Comment)\(([0-9]+),([0-9]+)\)-->!is', '', $obj->content);

		// Return error if content is empty.
		if (!$manual_updated && is_empty_html_content($obj->content))
		{
			return new BaseObject(-1, 'msg_empty_content');
		}

		// if use editor of nohtml, Remove HTML tags from the contents.
		if(!$manual_updated || isset($obj->allow_html) || isset($obj->use_html))
		{
			$obj->content = EditorModel::converter($obj, 'document');
		}

		// Remove iframe and script if not a top adminisrator in the session.
		if($logged_info->is_admin != 'Y')
		{
			$obj->content = removeHackTag($obj->content);
		}

		// Fix encoding of non-BMP UTF-8 characters.
		$obj->title = utf8_mbencode($obj->title);
		$obj->content = utf8_mbencode($obj->content);

		// Begin transaction
		$oDB = DB::getInstance();
		$oDB->begin();

		// Insert history
		$bUseHistory = $document_config->use_history == 'Y' || $document_config->use_history == 'Trace';
		if($bUseHistory)
		{
			$args = new stdClass;
			$args->history_srl = getNextSequence();
			$args->document_srl = $obj->document_srl;
			$args->module_srl = $obj->module_srl;
			if($document_config->use_history == 'Y') $args->content = $source_obj->get('content');
			$args->nick_name = $source_obj->get('nick_name');
			$args->member_srl = $source_obj->get('member_srl');
			$args->regdate = $source_obj->get('last_update');
			$args->ipaddress = $source_obj->get('ipaddress');
			$output = executeQuery("document.insertHistory", $args);
		}

		// Set lang_code if the original document doesn't have it.
		if (!$source_obj->get('lang_code'))
		{
			$output = executeQuery('document.updateDocumentsLangCode', [
				'document_srl' => $source_obj->get('document_srl'),
				'lang_code' => Context::getLangType(),
			]);
		}
		// Move content to extra vars if the current language is different from the original document's lang_code.
		elseif ($source_obj->get('lang_code') !== Context::getLangType())
		{
			$extra_content = new stdClass;
			$extra_content->title = $obj->title;
			$extra_content->content = $obj->content;

			$document_output = executeQuery('document.getDocument', ['document_srl' => $source_obj->get('document_srl')], ['title', 'content']);
			if (isset($document_output->data->title))
			{
				$obj->title = $document_output->data->title;
				$obj->content = $document_output->data->content;
			}
		}

		// Insert data into the DB
		$output = executeQuery('document.updateDocument', $obj);
		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}

		// Remove all extra variables
		$extra_vars = array();
		if(Context::get('act')!='procFileDelete')
		{
			// Get a copy of current extra vars before deleting all existing data.
			$old_extra_vars = DocumentModel::getExtraVars($obj->module_srl, $obj->document_srl);
			$this->deleteDocumentExtraVars($source_obj->get('module_srl'), $obj->document_srl, null, Context::getLangType());

			// Insert extra variables if the document successfully inserted.
			$extra_keys = DocumentModel::getExtraKeys($obj->module_srl);
			if(count($extra_keys))
			{
				foreach($extra_keys as $idx => $extra_item)
				{
					$value = NULL;
					if(isset($obj->{'extra_vars'.$idx}))
					{
						$tmp = $obj->{'extra_vars'.$idx};
						if ($extra_item->type === 'file')
						{
							$value = $tmp;
						}
						elseif (is_array($tmp))
						{
							$value = implode('|@|', $tmp);
						}
						else
						{
							$value = trim($tmp);
						}
					}
					elseif (isset($obj->{$extra_item->name}))
					{
						$value = trim($obj->{$extra_item->name});
					}

					// Validate and process the extra value.
					if ($value == NULL && $manual_updated && $extra_item->type !== 'file')
					{
						continue;
					}
					else
					{
						// Check for required and strict values.
						if (!$manual_updated)
						{
							$ev_output = $extra_item->validate($value, $old_extra_vars[$idx]->value ?? null);
							if ($ev_output && !$ev_output->toBool())
							{
								$oDB->rollback();
								return $ev_output;
							}
						}

						// Handle extra vars that support file upload.
						if ($extra_item->type === 'file')
						{
							// New upload
							if (is_array($value) && isset($value['name']))
							{
								// Delete old file
								if (isset($old_extra_vars[$idx]->value))
								{
									$fc_output = FileController::getInstance()->deleteFile($old_extra_vars[$idx]->value);
									if (!$fc_output->toBool())
									{
										$oDB->rollback();
										return $fc_output;
									}
								}
								// Insert new file
								$ev_output = $extra_item->uploadFile($value, $obj->document_srl, 'doc');
								if (!$ev_output->toBool())
								{
									$oDB->rollback();
									return $ev_output;
								}
								$value = $ev_output->get('file_srl');
							}
							// Delete current file
							elseif (isset($obj->{'_delete_extra_vars'.$idx}) && $obj->{'_delete_extra_vars'.$idx} === 'Y')
							{
								if (isset($old_extra_vars[$idx]->value))
								{
									// Check if deletion is allowed
									$ev_output = $extra_item->validate(null);
									if ($ev_output && !$ev_output->toBool())
									{
										$oDB->rollback();
										return $ev_output;
									}
									// Delete old file
									$fc_output = FileController::getInstance()->deleteFile($old_extra_vars[$idx]->value);
									if (!$fc_output->toBool())
									{
										$oDB->rollback();
										return $fc_output;
									}
								}
							}
							// Leave current file unchanged
							elseif (!$value)
							{
								if (isset($old_extra_vars[$idx]->value))
								{
									$value = $old_extra_vars[$idx]->value;
								}
							}
						}
					}
					$extra_vars[$extra_item->name] = $value;
					$this->insertDocumentExtraVar($obj->module_srl, $obj->document_srl, $idx, $value, $extra_item->eid);
				}
			}

			// Inert extra vars for multi-language support of title and contents.
			if (isset($extra_content))
			{
				$this->insertDocumentExtraVar($obj->module_srl, $obj->document_srl, -1, $extra_content->title, 'title_'.Context::getLangType());
				$this->insertDocumentExtraVar($obj->module_srl, $obj->document_srl, -2, $extra_content->content, 'content_'.Context::getLangType());
			}
		}

		// Clear extra_vars cache (#1969)
		self::clearDocumentCache($obj->document_srl, 'extra_vars');

		// Update the category if the category_srl exists.
		if($source_obj->get('category_srl') != $obj->category_srl || $source_obj->get('module_srl') == $logged_info->member_srl)
		{
			if($source_obj->get('category_srl') != $obj->category_srl)
			{
				$this->updateCategoryCount($obj->module_srl, $source_obj->get('category_srl'), '-1');
			}
			if($obj->category_srl)
			{
				$this->updateCategoryCount($obj->module_srl, $obj->category_srl, '+1');
			}
		}

		// Update log
		if($obj->update_log_setting === 'Y')
		{
			$obj->extra_vars = serialize($extra_vars);
			if($grant->manager)
			{
				$obj->is_admin = 'Y';
			}
			$update_output = $this->insertDocumentUpdateLog($obj, $source_obj);
			if(!$update_output->toBool())
			{
				$oDB->rollback();
				return $update_output;
			}
		}

		// Update attached file count
		$attachOutput = FileController::getInstance()->setFilesValid($obj->document_srl, 'doc');
		if(!$attachOutput->toBool())
		{
			$oDB->rollback();
			return $attachOutput;
		}
		$obj->updated_file_count = $attachOutput->get('updated_file_count');

		// Call a trigger (after)
		ModuleHandler::triggerCall('document.updateDocument', 'after', $obj);
		if ($is_publish)
		{
			ModuleHandler::triggerCall('document.publishDocument', 'after', $obj);
		}

		// commit
		$oDB->commit();

		// Remove the thumbnail file
		Rhymix\Framework\Storage::deleteDirectory(RX_BASEDIR . sprintf('files/thumbnails/%s', getNumberingPath($obj->document_srl, 3)));

		$output->add('document_srl', $obj->document_srl);
		$output->add('category_srl', $obj->category_srl);

		//remove from cache
		self::clearDocumentCache($obj->document_srl);
		return $output;
	}

	function insertDocumentUpdateLog($obj, $source_obj = null)
	{
		$update_args = new stdClass();
		$logged_info = Context::get('logged_info');
		if($source_obj === null)
		{
			$update_args->category_srl = $obj->category_srl;
			$update_args->module_srl = $obj->module_srl;
			$update_args->nick_name = $obj->nick_name;
		}
		else
		{
			if($obj->category_srl)
			{
				$update_args->category_srl = $obj->category_srl;
			}
			else
			{
				$update_args->category_srl = $source_obj->get('category_srl');
			}
			$update_args->module_srl = $source_obj->get('module_srl');
			$update_args->nick_name = $source_obj->get('nick_name');
		}

		$update_args->document_srl = $obj->document_srl;
		$update_args->update_member_srl = intval($logged_info->member_srl ?? 0);
		$update_args->title = $obj->title;
		$update_args->title_bold = $obj->title_bold ?? 'N';
		$update_args->title_color = $obj->title_color ?? null;
		$update_args->content = $obj->content;
		$update_args->update_nick_name = strval($logged_info->nick_name ?? $obj->nick_name);
		$update_args->tags = $obj->tags;
		$update_args->extra_vars = $obj->extra_vars;
		$update_args->reason_update = $obj->reason_update ?? '';
		$update_args->is_admin = $obj->is_admin;
		$update_output = executeQuery('document.insertDocumentUpdateLog', $update_args);

		return $update_output;
	}

	/**
	 * Deleting Documents
	 * @param int $document_srl
	 * @param bool $skip_grant_check
	 * @param bool $isEmptyTrash
	 * @param documentItem $oDocument
	 * @return object
	 */
	function deleteDocument($document_srl, $skip_grant_check = false, $isEmptyTrash = false, $oDocument = null)
	{
		// Call a trigger (before)
		$trigger_obj = new stdClass();
		$trigger_obj->document_srl = $document_srl;
		$trigger_obj->isEmptyTrash = $isEmptyTrash ? true : false;
		$output = ModuleHandler::triggerCall('document.deleteDocument', 'before', $trigger_obj);
		if(!$output->toBool()) return $output;

		// Check if the document exists
		if(!$isEmptyTrash)
		{
			$oDocument = DocumentModel::getDocument($document_srl);
		}
		else if($isEmptyTrash && $oDocument == null)
		{
			return new BaseObject(-1, 'msg_not_founded');
		}

		// Check permission
		if(!$oDocument->isExists())
		{
			return new BaseObject(-1, 'msg_invalid_document');
		}
		if(!$skip_grant_check && !$oDocument->isGranted())
		{
			return new BaseObject(-1, 'msg_not_permitted');
		}

		// begin transaction
		$oDB = DB::getInstance();
		$oDB->begin();

		//if empty trash, document already deleted, therefore document not delete
		$args = new stdClass();
		$args->document_srl = $document_srl;
		if(!$isEmptyTrash)
		{
			// Delete the document
			$output = executeQuery('document.deleteDocument', $args);
			if(!$output->toBool())
			{
				$oDB->rollback();
				return $output;
			}
		}

		$this->deleteDocumentAliasByDocument($document_srl);
		$this->deleteDocumentHistory(null, $document_srl, null);
		// Update category information if the category_srl exists.
		if($oDocument->get('category_srl'))
		{
			$this->updateCategoryCount($oDocument->get('module_srl'),$oDocument->get('category_srl'), '-1');
		}
		// Delete a declared list
		executeQuery('document.deleteDeclared', $args);
		// Delete extra variable
		$this->deleteDocumentExtraVars($oDocument->get('module_srl'), $oDocument->document_srl);

		// Call a trigger (after)
		$trigger_obj = $oDocument->getObjectVars();
		$trigger_obj->isEmptyTrash = $isEmptyTrash ? true : false;
		ModuleHandler::triggerCall('document.deleteDocument', 'after', $trigger_obj);

		// declared document, log delete
		$this->_deleteDeclaredDocuments($args);
		$this->_deleteDocumentReadedLog($args);
		$this->_deleteDocumentVotedLog($args);
		$this->_deleteDocumentUpdateLog($args);

		// commit
		$oDB->commit();

		// Remove the thumbnail file
		Rhymix\Framework\Storage::deleteDirectory(RX_BASEDIR . sprintf('files/thumbnails/%s', getNumberingPath($document_srl, 3)));

		// Remove from cache
		self::clearDocumentCache($document_srl);
		return $output;
	}

	/**
	 * Delete declared document, log
	 * @param string $documentSrls (ex: 1, 2,56, 88)
	 * @return void
	 */
	function _deleteDeclaredDocuments($documentSrls)
	{
		executeQuery('document.deleteDeclaredDocuments', $documentSrls);
		executeQuery('document.deleteDocumentDeclaredLog', $documentSrls);
	}

	/**
	 * Delete readed log
	 * @param string $documentSrls (ex: 1, 2,56, 88)
	 * @return void
	 */
	function _deleteDocumentReadedLog($documentSrls)
	{
		executeQuery('document.deleteDocumentReadedLog', $documentSrls);
	}

	/**
	 * Delete voted log
	 * @param string $documentSrls (ex: 1, 2,56, 88)
	 * @return void
	 */
	function _deleteDocumentVotedLog($documentSrls)
	{
		executeQuery('document.deleteDocumentVotedLog', $documentSrls);
	}

	function _deleteDocumentUpdateLog($document_srl)
	{
		executeQuery('document.deleteDocumentUpdateLog', $document_srl);
	}

	/**
	 * Move the doc into the trash
	 * @param object $obj
	 * @return object
	 */
	function moveDocumentToTrash($obj)
	{
		// Check the document and grants
		$oDocument = DocumentModel::getDocument($obj->document_srl);
		if(!$oDocument->isExists())
		{
			return new BaseObject(-1, 'msg_not_founded');
		}
		if(!$oDocument->isGranted())
		{
			return new BaseObject(-1, 'msg_not_permitted');
		}
		if($this->user->is_admin !== 'Y')
		{
			$member_info = MemberModel::getMemberInfo($oDocument->get('member_srl'));
			if($member_info->is_admin === 'Y')
			{
				return new BaseObject(-1, 'msg_admin_document_no_move_to_trash');
			}
		}
		if($oDocument->get('module_srl') == 0)
		{
			return new BaseObject(-1, 'Cannot throw data from the trash to the trash');
		}

		// Call trigger (before).
		$trigger_output = ModuleHandler::triggerCall('document.moveDocumentToTrash', 'before', $obj);
		if (!$trigger_output->toBool())
		{
			return $trigger_output;
		}

		// Create trash object.
		require_once(RX_BASEDIR.'modules/trash/model/TrashVO.php');
		$oTrashVO = new TrashVO();
		$oTrashVO->setTrashSrl(getNextSequence());
		$oTrashVO->setTitle($oDocument->variables['title']);
		$oTrashVO->setOriginModule('document');
		$oTrashVO->setSerializedObject(serialize($oDocument->variables));
		$oTrashVO->setDescription($obj->description ?? '');

		// begin transaction
		$oDB = DB::getInstance();
		$oDB->begin();

		$oTrashAdminController = TrashAdminController::getInstance();
		$output = $oTrashAdminController->insertTrash($oTrashVO);
		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}

		$output = executeQuery('document.deleteDocument', ['document_srl' => $oDocument->document_srl]);
		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}

		// update category
		if ($oDocument->get('category_srl'))
		{
			$this->updateCategoryCount($oDocument->get('module_srl'), $oDocument->get('category_srl'), '-1');
		}

		// Set the attachment to be invalid state
		/*
		if($oDocument->hasUploadedFiles())
		{
			$args = new stdClass();
			$args->upload_target_srl = $oDocument->document_srl;
			$args->isvalid = 'N';
			executeQuery('file.updateFileValid', $args);
		}
		*/

		// Call a trigger (after)
		$obj->trash_srl = $oTrashVO->getTrashSrl();
		$obj->module_srl = $oDocument->get('module_srl');
		$obj->member_srl = $oDocument->get('member_srl');
		$obj->regdate = $oDocument->get('regdate');
		$obj->last_update = $oDocument->get('last_update');
		ModuleHandler::triggerCall('document.moveDocumentToTrash', 'after', $obj);

		// commit
		$oDB->commit();

		// remove thumbnails
		Rhymix\Framework\Storage::deleteDirectory(RX_BASEDIR . sprintf('files/thumbnails/%s', getNumberingPath($obj->document_srl, 3)));

		// Clear cache
		self::clearDocumentCache($oDocument->document_srl);
		return $output;
	}

	/**
	 * Update read counts of the document
	 * @param documentItem $oDocument
	 * @return bool|void
	 */
	function updateReadedCount(&$oDocument)
	{
		// Pass if Crawler access
		if (\Rhymix\Framework\UA::isRobot())
		{
			return false;
		}

		// Get the view count option, and use the default if the value is empty or invalid.
		$valid_options = array(
			'all' => true,
			'some' => true,
			'once' => true,
			'none' => true,
		);

		$config = DocumentModel::getDocumentConfig();
		if (!isset($config->view_count_option) || !isset($valid_options[$config->view_count_option]))
		{
			$config->view_count_option = 'once';
		}

		// If not counting, return now.
		if ($config->view_count_option == 'none')
		{
			return false;
		}

		// Get document and user information.
		$document_srl = $oDocument->document_srl;
		$member_srl = abs($oDocument->get('member_srl'));
		$logged_info = Context::get('logged_info');

		// Option 'some': only count once per session.
		if ($config->view_count_option != 'all' && isset($_SESSION['readed_document'][$document_srl]))
		{
			return false;
		}

		// Option 'once': check member_srl and IP address.
		if ($config->view_count_option == 'once')
		{
			// Pass if the author's IP address is as same as visitor's.
			if($oDocument->get('ipaddress') == \RX_CLIENT_IP)
			{
				if (Context::getSessionStatus())
				{
					$_SESSION['readed_document'][$document_srl] = true;
				}
				return false;
			}

			// Pass if the author's member_srl is the same as the visitor's.
			if($member_srl && $logged_info && $logged_info->member_srl && $logged_info->member_srl == $member_srl)
			{
				$_SESSION['readed_document'][$document_srl] = true;
				return false;
			}
		}

		// Call a trigger when the read count is updated (before)
		$trigger_output = ModuleHandler::triggerCall('document.updateReadedCount', 'before', $oDocument);
		if(!$trigger_output->toBool()) return $trigger_output;

		// Update read counts
		$oDB = DB::getInstance();
		$oDB->begin();
		$args = new stdClass;
		$args->document_srl = $document_srl;
		executeQuery('document.updateReadedCount', $args);

		// Call a trigger when the read count is updated (after)
		ModuleHandler::triggerCall('document.updateReadedCount', 'after', $oDocument);
		$oDB->commit();

		// Register session
		if(!isset($_SESSION['readed_document'][$document_srl]) && Context::getSessionStatus())
		{
			$_SESSION['readed_document'][$document_srl] = true;
		}

		// Prevent session data getting too large
		if (is_array($_SESSION['readed_document']) && count($_SESSION['readed_document']) > 1000)
		{
			$_SESSION['readed_document'] = array_slice($_SESSION['readed_document'], 500, null, true);
		}

		return TRUE;
	}

	/**
	 * Insert extra variables into the document table
	 * @param int $module_srl
	 * @param int $var_idx
	 * @param string $var_name
	 * @param string $var_type
	 * @param string $var_is_required
	 * @param string $var_search
	 * @param string $var_default
	 * @param string $var_desc
	 * @param int $eid
	 * @param string $var_is_strict
	 * @param array $var_options
	 * @return object
	 */
	function insertDocumentExtraKey($module_srl, $var_idx, $var_name, $var_type, $var_is_required = 'N', $var_search = 'N', $var_default = '', $var_desc = '', $eid = 0, $var_is_strict = 'N', $var_options = null)
	{
		if (!$module_srl || !$var_idx || !$var_name || !$var_type || !$eid)
		{
			return new BaseObject(-1, 'msg_invalid_request');
		}

		$obj = new stdClass();
		$obj->module_srl = $module_srl;
		$obj->var_idx = $var_idx;
		$obj->var_name = $var_name;
		$obj->var_type = $var_type;
		$obj->var_is_required = $var_is_required=='Y'?'Y':'N';
		$obj->var_is_strict = $var_is_strict=='Y'?'Y':'N';
		$obj->var_search = $var_search=='Y'?'Y':'N';
		$obj->var_default = $var_default;
		$obj->var_options = $var_options ? json_encode($var_options, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES) : null;
		$obj->var_desc = $var_desc;
		$obj->eid = $eid;

		$output = executeQuery('document.getDocumentExtraKeys', $obj);
		if(!$output->data)
		{
			$output = executeQuery('document.insertDocumentExtraKey', $obj);
		}
		else
		{
			$output = executeQuery('document.updateDocumentExtraKey', $obj);
			// Update the extra var(eid)
			$output = executeQuery('document.updateDocumentExtraVar', $obj);
		}

		unset($GLOBALS['XE_EXTRA_KEYS'][$module_srl]);
		Rhymix\Framework\Cache::delete("site_and_module:module_document_extra_keys:$module_srl");
		return $output;
	}

	/**
	 * Remove the extra variables of the documents
	 * @param int $module_srl
	 * @param int $var_idx
	 * @return Object
	 */
	function deleteDocumentExtraKeys($module_srl, $var_idx = null)
	{
		if(!$module_srl) return new BaseObject(-1, 'msg_invalid_request');
		$obj = new stdClass();
		$obj->module_srl = $module_srl;
		if(!is_null($var_idx)) $obj->var_idx = $var_idx;

		$oDB = DB::getInstance();
		$oDB->begin();

		$output = $oDB->executeQuery('document.deleteDocumentExtraKeys', $obj);
		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}

		if($var_idx != NULL)
		{
			$output = $oDB->executeQuery('document.updateDocumentExtraKeyIdxOrder', $obj);
			if(!$output->toBool())
			{
				$oDB->rollback();
				return $output;
			}
		}

		$output =  executeQuery('document.deleteDocumentExtraVars', $obj);
		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}

		if($var_idx != NULL)
		{
			$output = $oDB->executeQuery('document.updateDocumentExtraVarIdxOrder', $obj);
			if(!$output->toBool())
			{
				$oDB->rollback();
				return $output;
			}
		}

		$oDB->commit();

		unset($GLOBALS['XE_EXTRA_KEYS'][$module_srl]);
		Rhymix\Framework\Cache::delete("site_and_module:module_document_extra_keys:$module_srl");
		return new BaseObject();
	}

	/**
	 * Insert extra vaiable to the documents table
	 * @param int $module_srl
	 * @param int $document_srl
	 * @param int|string $idx_or_eid
	 * @param mixed $value
	 * @param int $eid
	 * @param string $lang_code
	 * @return Object|void
	 */
	public static function insertDocumentExtraVar($module_srl, $document_srl, $idx_or_eid, $value, $eid = null, $lang_code = null)
	{
		if(!$module_srl || !$document_srl || !$idx_or_eid || !isset($value))
		{
			return new BaseObject(-1, 'msg_invalid_request');
		}

		if (is_int($idx_or_eid) || ctype_digit($idx_or_eid))
		{
			if (!$eid)
			{
				$eid = DocumentModel::getExtraVarEidByIdx($module_srl, $idx_or_eid);
				if (!$eid)
				{
					return new BaseObject(-1, 'Invalid idx: ' . $idx_or_eid);
				}
			}
		}
		else
		{
			$eid = $idx_or_eid;
			$idx_or_eid = DocumentModel::getExtraVarIdxByEid($module_srl, $eid);
			if (!$idx_or_eid)
			{
				return new BaseObject(-1, 'Invalid eid: ' . $eid);
			}
		}

		$obj = new stdClass;
		$obj->module_srl = $module_srl;
		$obj->document_srl = $document_srl;
		$obj->var_idx = $idx_or_eid;
		$obj->value = $value;
		$obj->lang_code = $lang_code ?: Context::getLangType();
		$obj->eid = $eid;

		return executeQuery('document.insertDocumentExtraVar', $obj);
	}

	/**
	 * Update extra vaiable in the documents table
	 * @param int $module_srl
	 * @param int $document_srl
	 * @param int|string $idx_or_eid
	 * @param mixed $value
	 * @param int $eid
	 * @param string $lang_code
	 * @return Object|void
	 */
	public static function updateDocumentExtraVar($module_srl, $document_srl, $idx_or_eid, $value, $eid = null, $lang_code = null)
	{
		if(!$module_srl || !$document_srl || !$idx_or_eid || !isset($value))
		{
			return new BaseObject(-1, 'msg_invalid_request');
		}

		if (is_int($idx_or_eid) || ctype_digit($idx_or_eid))
		{
			if (!$eid)
			{
				$eid = DocumentModel::getExtraVarEidByIdx($module_srl, $idx_or_eid);
				if (!$eid)
				{
					return new BaseObject(-1, 'Invalid idx: ' . $idx_or_eid);
				}
			}
		}
		else
		{
			$eid = $idx_or_eid;
			$idx_or_eid = DocumentModel::getExtraVarIdxByEid($module_srl, $eid);
			if (!$idx_or_eid)
			{
				return new BaseObject(-1, 'Invalid eid: ' . $eid);
			}
		}

		$obj = new stdClass;
		$obj->module_srl = $module_srl;
		$obj->document_srl = $document_srl;
		$obj->var_idx = $idx_or_eid;
		$obj->value = $value;
		$obj->lang_code = $lang_code ?: Context::getLangType();
		$obj->eid = $eid;

		$oDB = DB::getInstance();
		$oDB->begin();

		$output = self::deleteDocumentExtraVars($module_srl, $document_srl, $idx_or_eid, $lang_code, $eid);
		if (!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}

		$output = self::insertDocumentExtraVar($module_srl, $document_srl, $idx_or_eid, $value, $eid, $lang_code);
		if (!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}

		$oDB->commit();
		return $output;
	}

	/**
	 * Remove values of extra variable from the document
	 * @param int $module_srl
	 * @param int $document_srl
	 * @param int $var_idx
	 * @param string $lang_code
	 * @param int $eid
	 * @return $output
	 */
	public static function deleteDocumentExtraVars($module_srl, $document_srl = null, $var_idx = null, $lang_code = null, $eid = null)
	{
		$obj = new stdClass();
		$obj->module_srl = $module_srl;
		if(!is_null($document_srl)) $obj->document_srl = $document_srl;
		if(!is_null($var_idx)) $obj->var_idx = $var_idx;
		if(!is_null($lang_code)) $obj->lang_code = $lang_code;
		if(!is_null($eid)) $obj->eid = $eid;
		$output = executeQuery('document.deleteDocumentExtraVars', $obj);
		return $output;
	}


	/**
	 * Increase the number of vote-up of the document
	 * @param int $document_srl
	 * @param int $point
	 * @param bool $allow_same_ip
	 * @return Object
	 */
	function updateVotedCount($document_srl, $point = 1, $allow_same_ip = false)
	{
		if($point > 0)
		{
			$failed_voted = 'failed_voted';
		}
		else
		{
			$failed_voted = 'failed_blamed';
		}

		// Return fail if session already has information about votes
		if(!empty($_SESSION['voted_document'][$document_srl]))
		{
			return new BaseObject(-1, $failed_voted . '_already');
		}

		// Get the original document
		$oDocument = DocumentModel::getDocument($document_srl, false, false);

		// Pass if the author's IP address is as same as visitor's.
		if(!$allow_same_ip && $oDocument->get('ipaddress') == \RX_CLIENT_IP && !$this->user->isAdmin())
		{
			return new BaseObject(-1, $failed_voted);
		}

		// Get current member_srl
		$member_srl = MemberModel::getLoggedMemberSrl();

		// Check if document's author is a member.
		if($oDocument->get('member_srl'))
		{
			// Pass after registering a session if author's information is same as the currently logged-in user's.
			if($member_srl && $member_srl == abs($oDocument->get('member_srl')))
			{
				$_SESSION['voted_document'][$document_srl] = false;
				return new BaseObject(-1, $failed_voted . '_self');
			}
		}

		// Use member_srl for logged-in members and IP address for non-members.
		$args = new stdClass();
		if($member_srl)
		{
			$args->member_srl = $member_srl;
		}
		else
		{
			$args->member_srl = 0;
			$args->ipaddress = \RX_CLIENT_IP;
		}
		$args->document_srl = $document_srl;
		$output = executeQuery('document.getDocumentVotedLogInfo', $args);

		// Pass after registering a session if log information has vote-up logs
		if($output->data->count)
		{
			$_SESSION['voted_document'][$document_srl] = false;
			return new BaseObject(-1, $failed_voted);
		}

		// Call a trigger (before)
		$trigger_obj = new stdClass;
		$trigger_obj->member_srl = $oDocument->get('member_srl');
		$trigger_obj->module_srl = $oDocument->get('module_srl');
		$trigger_obj->document_srl = $oDocument->get('document_srl');
		$trigger_obj->update_target = ($point < 0) ? 'blamed_count' : 'voted_count';
		$trigger_obj->point = $point;
		$trigger_obj->before_point = ($point < 0) ? $oDocument->get('blamed_count') : $oDocument->get('voted_count');
		$trigger_obj->after_point = $trigger_obj->before_point + $point;
		$trigger_obj->cancel = false;
		$trigger_output = ModuleHandler::triggerCall('document.updateVotedCount', 'before', $trigger_obj);
		if(!$trigger_output->toBool())
		{
			return $trigger_output;
		}

		// begin transaction
		$oDB = DB::getInstance();
		$oDB->begin();

		// Update the voted count
		if($trigger_obj->update_target === 'blamed_count')
		{
			$args->blamed_count = $trigger_obj->after_point;
			$output = executeQuery('document.updateBlamedCount', $args);
		}
		else
		{
			$args->voted_count = $trigger_obj->after_point;
			$output = executeQuery('document.updateVotedCount', $args);
		}
		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}

		// Leave in the session information
		$_SESSION['voted_document'][$document_srl] = $trigger_obj->point;

		// Leave logs
		$args->point = $trigger_obj->point;
		$output = executeQuery('document.insertDocumentVotedLog', $args);
		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}

		// Call a trigger (after)
		ModuleHandler::triggerCall('document.updateVotedCount', 'after', $trigger_obj);

		$oDB->commit();

		//remove document item from cache
		Rhymix\Framework\Cache::delete('document_item:' . getNumberingPath($document_srl) . $document_srl);

		// Return result
		$output = new BaseObject();
		if($trigger_obj->update_target === 'voted_count')
		{
			$output->setMessage('success_voted');
			$output->add('voted_count', $trigger_obj->after_point);
		}
		else
		{
			$output->setMessage('success_blamed');
			$output->add('blamed_count', $trigger_obj->after_point);
		}

		// Prevent session data getting too large
		if (count($_SESSION['voted_document']) > 200)
		{
			$_SESSION['voted_document'] = array_slice($_SESSION['voted_document'], 100, null, true);
		}

		return $output;
	}

	/**
	 * Report posts
	 * @param int $document_srl
	 * @param string $declare_message
	 * @return void|Object
	 */
	function declaredDocument($document_srl, $declare_message = '')
	{
		// Fail if session already tried to report the document
		if(!empty($_SESSION['declared_document'][$document_srl]))
		{
			return new BaseObject(-1, 'failed_declared_already');
		}

		// Check if previously reported
		$args = new stdClass();
		$args->document_srl = $document_srl;
		$output = executeQuery('document.getDeclaredDocument', $args);
		if(!$output->toBool())
		{
			return $output;
		}

		$declared_count = ($output->data->declared_count) ? $output->data->declared_count : 0;
		$declare_message = trim(htmlspecialchars($declare_message));

		$trigger_obj = new stdClass();
		$trigger_obj->document_srl = $document_srl;
		$trigger_obj->declared_count = $declared_count;
		$trigger_obj->declare_message = $declare_message;

		// Call a trigger (before)
		$trigger_output = ModuleHandler::triggerCall('document.declaredDocument', 'before', $trigger_obj);
		if(!$trigger_output->toBool())
		{
			return $trigger_output;
		}

		// Get the original document
		$oDocument = DocumentModel::getDocument($document_srl, false, false);

		// Pass if the author's IP address is as same as visitor's.
		$module_srl = $oDocument->get('module_srl');
		$document_config = ModuleModel::getModulePartConfig('document', $module_srl);
		$allow_same_ip = ($document_config->allow_declare_from_same_ip ?? 'N') === 'Y';
		if(!$allow_same_ip && $oDocument->get('ipaddress') == \RX_CLIENT_IP && !$this->user->isAdmin())
		{
			return new BaseObject(-1, 'failed_declared');
		}

		// Get currently logged in user.
		$member_srl = $this->user->member_srl;

		// Check if document's author is a member.
		if($oDocument->get('member_srl'))
		{
			// Pass after registering a session if author's information is same as the currently logged-in user's.
			if($member_srl && $member_srl == abs($oDocument->get('member_srl')))
			{
				$_SESSION['declared_document'][$document_srl] = false;
				return new BaseObject(-1, 'failed_declared_self');
			}
		}

		// Pass after registering a sesson if reported/declared documents are in the logs.
		$args = new stdClass;
		$args->document_srl = $document_srl;
		if($member_srl)
		{
			$args->member_srl = $member_srl;
		}
		else
		{
			$args->ipaddress = \RX_CLIENT_IP;
		}
		$output = executeQuery('document.getDocumentDeclaredLogInfo', $args);
		if($output->data->count)
		{
			$_SESSION['declared_document'][$document_srl] = false;
			return new BaseObject(-1, 'failed_declared_already');
		}

		// Fill in remaining information for logging.
		$args->member_srl = $member_srl;
		$args->ipaddress = \RX_CLIENT_IP;
		$args->declare_message = $declare_message;

		// begin transaction
		$oDB = DB::getInstance();
		$oDB->begin();

		// Add the declared document
		if($declared_count > 0)
		{
			$output = executeQuery('document.updateDeclaredDocument', $args);
		}
		else
		{
			$output = executeQuery('document.insertDeclaredDocument', $args);
		}

		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}

		// Leave logs
		$output = executeQuery('document.insertDocumentDeclaredLog', $args);
		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}

		$this->add('declared_count', $declared_count + 1);

		// Send message to admin
		$message_targets = array();
		if ($document_config->declared_message && in_array('admin', $document_config->declared_message))
		{
			$output = executeQueryArray('member.getAdmins', new stdClass);
			foreach ($output->data as $admin)
			{
				$message_targets[$admin->member_srl] = true;
			}
		}
		if ($document_config->declared_message && in_array('manager', $document_config->declared_message))
		{
			$output = executeQueryArray('module.getModuleAdmin', (object)['module_srl' => $module_srl]);
			foreach ($output->data as $manager)
			{
				$message_targets[$manager->member_srl] = true;
			}
		}
		if ($message_targets)
		{
			$oCommunicationController = CommunicationController::getInstance();
			$message_title = lang('document.declared_message_title');
			$message_content = sprintf('<p><a href="%s">%s</a></p><p>%s</p>', $oDocument->getPermanentUrl(), $oDocument->getTitleText(), $declare_message);
			foreach ($message_targets as $target_member_srl => $val)
			{
				$oCommunicationController->sendMessage($this->user->member_srl, $target_member_srl, $message_title, $message_content, false, null, false);
			}
		}

		// Call a trigger (after)
		$trigger_obj->declared_count = $declared_count + 1;
		ModuleHandler::triggerCall('document.declaredDocument', 'after', $trigger_obj);

		// commit
		$oDB->commit();

		// Leave in the session information
		$_SESSION['declared_document'][$document_srl] = true;

		// Prevent session data getting too large
		if (count($_SESSION['declared_document']) > 200)
		{
			$_SESSION['declared_document'] = array_slice($_SESSION['declared_document'], 100, null, true);
		}

		$this->setMessage('success_declared');
	}

	/**
	 * 신고 취소
	 * @param $document_srl
	 * @return BaseObject|object|void
	 */
	function declaredDocumentCancel($document_srl)
	{
		$member_srl = $this->user->member_srl;
		if(!$_SESSION['declared_document'][$document_srl] && !$member_srl)
		{
			return new BaseObject(-1, 'failed_declared_cancel');
		}

		// Get the original document
		$oDocument = DocumentModel::getDocument($document_srl, false, false);

		$oDB = DB::getInstance();
		$oDB->begin();

		$args = new stdClass;
		$args->document_srl = $document_srl;
		if($member_srl)
		{
			$args->member_srl = $member_srl;
		}
		else
		{
			$args->ipaddress = \RX_CLIENT_IP;
		}
		$output = executeQuery('document.getDocumentDeclaredLogInfo', $args);
		if(!isset($output->data->count) || !$output->data->count)
		{
			unset($_SESSION['declared_document'][$document_srl]);
			return new BaseObject(-1, 'failed_declared_cancel');
		}

		// Get current declared count
		$args = new stdClass();
		$args->document_srl = $document_srl;
		$output = executeQuery('document.getDeclaredDocument', $args);
		$declared_count = ($output->data->declared_count) ? $output->data->declared_count : 0;

		// Call a trigger (before)
		$trigger_obj = new stdClass();
		$trigger_obj->document_srl = $document_srl;
		$trigger_obj->declared_count = $declared_count;
		$trigger_output = ModuleHandler::triggerCall('document.declaredDocumentCancel', 'before', $trigger_obj);
		if(!$trigger_output->toBool())
		{
			return $trigger_output;
		}

		if($declared_count > 1)
		{
			$output = executeQuery('document.updateDeclaredDocumentCancel', $args);
		}
		else
		{
			$output = executeQuery('document.deleteDeclaredDocument', $args);
		}
		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}

		$output = executeQuery('document.deleteDeclaredDocumentLog', $args);
		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}

		$message_targets = array();
		$module_srl = $oDocument->get('module_srl');
		$document_config = ModuleModel::getModulePartConfig('document', $module_srl);
		if ($document_config->declared_message && in_array('admin', $document_config->declared_message))
		{
			$output = executeQueryArray('member.getAdmins', new stdClass);
			foreach ($output->data as $admin)
			{
				$message_targets[$admin->member_srl] = true;
			}
		}
		if ($document_config->declared_message && in_array('manager', $document_config->declared_message))
		{
			$output = executeQueryArray('module.getModuleAdmin', (object)['module_srl' => $module_srl]);
			foreach ($output->data as $manager)
			{
				$message_targets[$manager->member_srl] = true;
			}
		}
		if ($message_targets)
		{
			$oCommunicationController = CommunicationController::getInstance();
			$message_title = lang('document.declared_cancel_message_title');
			$message_content = sprintf('<p><a href="%s">%s</a></p>', $oDocument->getPermanentUrl(), $oDocument->getTitleText());
			foreach ($message_targets as $target_member_srl => $val)
			{
				$oCommunicationController->sendMessage($this->user->member_srl, $target_member_srl, $message_title, $message_content, false. null, false);
			}
		}

		$oDB->commit();

		$trigger_obj->declared_count = $declared_count - 1;
		ModuleHandler::triggerCall('document.declaredDocumentCancel', 'after', $trigger_obj);

		unset($_SESSION['declared_document'][$document_srl]);

		$this->setMessage('success_declared_cancel');
	}

	/**
	 * Increase the number of comments in the document
	 * Update modified date, modifier, and order with increasing comment count
	 * @param int $document_srl
	 * @param int $comment_count
	 * @param string $last_updater
	 * @param bool $update_order
	 * @return object
	 */
	function updateCommentCount($document_srl, $comment_count, $last_updater = null, $update_order = false)
	{
		$args = new stdClass();
		$args->document_srl = $document_srl;
		$args->comment_count = $comment_count;

		if($update_order)
		{
			$args->update_order = -1*getNextSequence();
			$args->last_update = date('YmdHis');
			$args->last_updater = $last_updater;
		}

		// remove document item from cache
		Rhymix\Framework\Cache::delete('document_item:' . getNumberingPath($document_srl) . $document_srl);

		return executeQuery('document.updateCommentCount', $args);
	}

	/**
	 * Increase trackback count of the document
	 * @param int $document_srl
	 * @param int $trackback_count
	 * @return object
	 */
	function updateTrackbackCount($document_srl, $trackback_count)
	{
		$args = new stdClass;
		$args->document_srl = $document_srl;
		$args->trackback_count = $trackback_count;

		// remove document item from cache
		Rhymix\Framework\Cache::delete('document_item:' . getNumberingPath($document_srl) . $document_srl);

		return executeQuery('document.updateTrackbackCount', $args);
	}

	/**
	 * Add a category
	 * @param object $obj
	 * @return object
	 */
	function insertCategory($obj)
	{
		// Sort the order to display if a child category is added
		if($obj->parent_srl)
		{
			// Get its parent category
			$parent_category = DocumentModel::getCategory($obj->parent_srl);
			$obj->list_order = $parent_category->list_order;
			$this->updateCategoryListOrder($parent_category->module_srl, $parent_category->list_order+1);
			if(!$obj->category_srl) $obj->category_srl = getNextSequence();
		}
		else
		{
			$obj->list_order = $obj->category_srl = getNextSequence();
		}

		$output = executeQuery('document.insertCategory', $obj);
		if($output->toBool())
		{
			$output->add('category_srl', $obj->category_srl);
			$this->makeCategoryFile($obj->module_srl);
		}

		return $output;
	}

	/**
	 * Increase list_count from a specific category
	 * @param int $module_srl
	 * @param int $list_order
	 * @return object
	 */
	function updateCategoryListOrder($module_srl, $list_order)
	{
		$args = new stdClass;
		$args->module_srl = $module_srl;
		$args->list_order = $list_order;
		return executeQuery('document.updateCategoryOrder', $args);
	}

	/**
	 * Update document_count in the category.
	 * @param int $module_srl
	 * @param int $category_srl
	 * @param int|string $document_count
	 * @return object
	 */
	function updateCategoryCount($module_srl, $category_srl, $document_count = 0)
	{
		// Create a document model object
		if (preg_match('/^[+-]/', (string)$document_count))
		{
			$document_count = intval($document_count, 10);
			$mode = 'document_count_diff';
		}
		else
		{
			$document_count = $document_count ?: DocumentModel::getCategoryDocumentCount($module_srl,$category_srl);
			$mode = 'document_count';
		}

		$args = new stdClass;
		$args->category_srl = $category_srl;
		$args->{$mode} = $document_count;
		$output = executeQuery('document.updateCategoryCount', $args);
		if($output->toBool()) $this->makeCategoryFile($module_srl);

		return $output;
	}

	/**
	 * Update category information
	 * @param object $obj
	 * @return object
	 */
	function updateCategory($obj)
	{
		$output = executeQuery('document.updateCategory', $obj);
		if($output->toBool()) $this->makeCategoryFile($obj->module_srl);
		return $output;
	}

	/**
	 * Delete a category
	 * @param int $category_srl
	 * @return object
	 */
	function deleteCategory($category_srl)
	{
		$args = new stdClass();
		$args->category_srl = $category_srl;
		$category_info = DocumentModel::getCategory($category_srl);
		// Display an error that the category cannot be deleted if it has a child
		$output = executeQuery('document.getChildCategoryCount', $args);
		if(!$output->toBool()) return $output;
		if($output->data->count>0) return new BaseObject(-1, 'msg_cannot_delete_for_child');
		// Delete a category information
		$output = executeQuery('document.deleteCategory', $args);
		if(!$output->toBool()) return $output;

		$this->makeCategoryFile($category_info->module_srl);

		// remove cache
		$page = 0;
		while(true)
		{
			$args = new stdClass();
			$args->category_srl = $category_srl;
			$args->list_count = 100;
			$args->page = ++$page;
			$output = executeQuery('document.getDocumentList', $args, array('document_srl'));

			if($output->data == array())
			{
				break;
			}

			foreach($output->data as $val)
			{
				self::clearDocumentCache($val->document_srl);
			}
		}

		// Update category_srl of the documents in the same category to 0
		$args = new stdClass();
		$args->target_category_srl = 0;
		$args->source_category_srl = $category_srl;
		$output = executeQuery('document.updateDocumentCategory', $args);

		return $output;
	}

	/**
	 * Delete all categories in a module
	 * @param int $module_srl
	 * @return object
	 */
	function deleteModuleCategory($module_srl)
	{
		$args = new stdClass();
		$args->module_srl = $module_srl;
		$output = executeQuery('document.deleteModuleCategory', $args);
		return $output;
	}

	/**
	 * Move the category level to higher
	 * @param int $category_srl
	 * @return Object
	 */
	function moveCategoryUp($category_srl)
	{
		// Get information of the selected category
		$args = new stdClass;
		$args->category_srl = $category_srl;
		$output = executeQuery('document.getCategory', $args);

		$category = $output->data;
		$list_order = $category->list_order;
		$module_srl = $category->module_srl;
		// Seek a full list of categories
		$category_list = DocumentModel::getCategoryList($module_srl);
		$category_srl_list = array_keys($category_list);
		if(count($category_srl_list)<2) return new BaseObject();

		$prev_category = NULL;
		foreach($category_list as $key => $val)
		{
			if($key==$category_srl) break;
			$prev_category = $val;
		}
		// Return if the previous category doesn't exist
		if(!$prev_category) return new BaseObject(-1, 'msg_category_not_moved');
		// Return if the selected category is the top level
		if($category_srl_list[0]==$category_srl) return new BaseObject(-1, 'msg_category_not_moved');
		// Information of the selected category
		$cur_args = new stdClass;
		$cur_args->category_srl = $category_srl;
		$cur_args->list_order = $prev_category->list_order;
		$cur_args->title = $category->title;
		$this->updateCategory($cur_args);
		// Category information
		$prev_args = new stdClass;
		$prev_args->category_srl = $prev_category->category_srl;
		$prev_args->list_order = $list_order;
		$prev_args->title = $prev_category->title;
		$this->updateCategory($prev_args);

		return new BaseObject();
	}

	/**
	 * Move the category down
	 * @param int $category_srl
	 * @return Object
	 */
	function moveCategoryDown($category_srl)
	{
		// Get information of the selected category
		$args = new stdClass;
		$args->category_srl = $category_srl;
		$output = executeQuery('document.getCategory', $args);

		$category = $output->data;
		$list_order = $category->list_order;
		$module_srl = $category->module_srl;
		// Seek a full list of categories
		$category_list = DocumentModel::getCategoryList($module_srl);
		$category_srl_list = array_keys($category_list);
		if(count($category_srl_list)<2) return new BaseObject();

		for($i=0;$i<count($category_srl_list);$i++)
		{
			if($category_srl_list[$i]==$category_srl) break;
		}

		$next_category_srl = $category_srl_list[$i+1];
		if(!$category_list[$next_category_srl]) return new BaseObject(-1, 'msg_category_not_moved');
		$next_category = $category_list[$next_category_srl];
		// Information of the selected category
		$cur_args = new stdClass;
		$cur_args->category_srl = $category_srl;
		$cur_args->list_order = $next_category->list_order;
		$cur_args->title = $category->title;
		$this->updateCategory($cur_args);
		// Category information
		$next_args = new stdClass;
		$next_args->category_srl = $next_category->category_srl;
		$next_args->list_order = $list_order;
		$next_args->title = $next_category->title;
		$this->updateCategory($next_args);

		return new BaseObject();
	}

	/**
	 * Add javascript codes into the header by checking values of document_extra_keys type, required and others
	 * @param int $module_srl
	 * @return void
	 */
	function addXmlJsFilter($module_srl)
	{
		$extra_keys = DocumentModel::getExtraKeys($module_srl);
		if(!count($extra_keys)) return;

		$js_code = array();
		$js_code[] = '<script>//<![CDATA[';
		$js_code[] = '(function($){';
		$js_code[] = 'var validator = xe.getApp("validator")[0];';
		$js_code[] = 'if(!validator) return false;';

		foreach($extra_keys as $idx => $val)
		{
			$idx = $val->idx;
			if($val->type == 'kr_zip')
			{
				$idx .= '[]';
			}
			$js_code[] = sprintf('validator.cast("ADD_MESSAGE", ["extra_vars%s", %s]);', $idx, var_export($val->name, true));
			if($val->is_required == 'Y' && $val->type !== 'file')
			{
				$js_code[] = sprintf('validator.cast("ADD_EXTRA_FIELD", ["extra_vars%s", { required:true }]);', $idx);
			}
		}

		$js_code[] = '})(jQuery);';
		$js_code[] = '//]]></script>';
		$js_code   = implode("\n", $js_code);

		Context::addHtmlHeader($js_code);
	}

	/**
	 * Add a category
	 * @param object $args
	 * @return void
	 */
	function procDocumentInsertCategory($args = null)
	{
		// List variables
		if(!$args) $args = Context::gets('module_srl','category_srl','parent_srl','category_title','category_description','expand','is_default','group_srls','category_color','mid');
		$args->title = trim($args->category_title);
		$args->description = trim($args->category_description);
		$args->color = trim($args->category_color);
		$args->expand = (isset($args->expand) && $args->expand === 'Y') ? 'Y' : 'N';
		$args->is_default = (isset($args->is_default) && $args->is_default === 'Y') ? 'Y' : 'N';

		if(!$args->module_srl && $args->mid)
		{
			unset($args->mid);
			$args->module_srl = $this->module_srl;
		}

		// Check permissions
		$columnList = array('module_srl', 'module');
		$module_info = ModuleModel::getModuleInfoByModuleSrl($args->module_srl, $columnList);
		$grant = ModuleModel::getGrant($module_info, Context::get('logged_info'));
		if(!$grant->manager) return new BaseObject(-1, 'msg_not_permitted');

		if (!is_array($args->group_srls))
		{
			$args->group_srls = str_replace('|@|',',',$args->group_srls);
		}
		else
		{
			$args->group_srls = implode(',', $args->group_srls);
		}
		$args->parent_srl = (int)$args->parent_srl;

		$oDB = DB::getInstance();
		$oDB->begin();

		// Check if already exists
		if($args->category_srl)
		{
			$category_info = DocumentModel::getCategory($args->category_srl);
			if($category_info->category_srl != $args->category_srl) $args->category_srl = null;
		}

		// Update if exists
		if($args->category_srl)
		{
			$output = $this->updateCategory($args);
			if(!$output->toBool())
			{
				$oDB->rollback();
				return $output;
			}
			// Insert if not exist
		}
		else
		{
			$output = $this->insertCategory($args);
			if(!$output->toBool())
			{
				$oDB->rollback();
				return $output;
			}
		}

		// If set as default, set other categories as not default.
		if ($args->is_default === 'Y')
		{
			$output = executeQuery('document.updateCategoryIsDefault', [
				'module_srl' => $args->module_srl,
				'except_category_srl' => $args->category_srl,
			]);
		}

		// Update the xml file and get its location
		$xml_file = $this->makeCategoryFile($args->module_srl);

		$oDB->commit();

		$this->add('xml_file', $xml_file);
		$this->add('module_srl', $args->module_srl);
		$this->add('category_srl', $args->category_srl);
		$this->add('parent_srl', $args->parent_srl);

		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : Context::get('error_return_url');
		$this->setRedirectUrl($returnUrl);
	}

	/**
	 * Move a category
	 * @return void
	 */
	function procDocumentMoveCategory()
	{
		$source_category_srl = Context::get('source_srl');
		// If parent_srl exists, be the first child
		$parent_category_srl = Context::get('parent_srl');
		// If target_srl exists, be a sibling
		$target_category_srl = Context::get('target_srl');

		$source_category = DocumentModel::getCategory($source_category_srl);
		// Check permissions
		$columnList = array('module_srl', 'module');
		$module_info = ModuleModel::getModuleInfoByModuleSrl($source_category->module_srl, $columnList);
		$grant = ModuleModel::getGrant($module_info, Context::get('logged_info'));
		if(!$grant->manager) return new BaseObject(-1, 'msg_not_permitted');

		// First child of the parent_category_srl
		$source_args = new stdClass;
		if($parent_category_srl > 0 || ($parent_category_srl == 0 && $target_category_srl == 0))
		{
			$parent_category = DocumentModel::getCategory($parent_category_srl);

			$args = new stdClass;
			$args->module_srl = $source_category->module_srl;
			$args->parent_srl = $parent_category_srl;
			$output = executeQuery('document.getChildCategoryMinListOrder', $args);

			if(!$output->toBool()) return $output;
			$args->list_order = (int)$output->data->list_order;
			if(!$args->list_order) $args->list_order = 0;
			$args->list_order--;

			$source_args->category_srl = $source_category_srl;
			$source_args->parent_srl = $parent_category_srl;
			$source_args->list_order = $args->list_order;
			$output = $this->updateCategory($source_args);
			if(!$output->toBool()) return $output;
			// Sibling of the $target_category_srl
		}
		else if($target_category_srl > 0)
		{
			$target_category = DocumentModel::getCategory($target_category_srl);
			// Move all siblings of the $target_category down
			$output = $this->updateCategoryListOrder($target_category->module_srl, $target_category->list_order+1);
			if(!$output->toBool()) return $output;

			$source_args->category_srl = $source_category_srl;
			$source_args->parent_srl = $target_category->parent_srl;
			$source_args->list_order = $target_category->list_order+1;
			$output = $this->updateCategory($source_args);
			if(!$output->toBool()) return $output;
		}
		// Re-generate the xml file
		$xml_file = $this->makeCategoryFile($source_category->module_srl);
		// Variable settings
		$this->add('xml_file', $xml_file);
		$this->add('source_category_srl', $source_category_srl);
	}

	/**
	 * Delete a category
	 * @return void
	 */
	function procDocumentDeleteCategory()
	{
		// List variables
		$args = Context::gets('module_srl','category_srl');

		// Check permissions
		$columnList = array('module_srl', 'module');
		$module_info = ModuleModel::getModuleInfoByModuleSrl($args->module_srl, $columnList);
		$grant = ModuleModel::getGrant($module_info, Context::get('logged_info'));
		if(!$grant->manager) return new BaseObject(-1, 'msg_not_permitted');

		// Get original information
		$category_info = DocumentModel::getCategory($args->category_srl);
		if($category_info->parent_srl) $parent_srl = $category_info->parent_srl;
		// Display an error that the category cannot be deleted if it has a child node
		if(DocumentModel::getCategoryChlidCount($args->category_srl))
		{
			return new BaseObject(-1, 'msg_cannot_delete_for_child');
		}

		$oDB = DB::getInstance();
		$oDB->begin();

		// Remove from the DB
		$output = $this->deleteCategory($args->category_srl);
		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}
		// Update the xml file and get its location
		$xml_file = $this->makeCategoryFile($args->module_srl);

		$oDB->commit();

		$this->add('xml_file', $xml_file);
		$this->add('category_srl', $parent_srl);
		$this->setMessage('success_deleted');
	}

	/**
	 * Xml files updated
	 * Occasionally the xml file is not generated after menu is configued on the admin page
	 * The administrator can manually update the file in this case
	 * Although the issue is not currently reproduced, it is unnecessay to remove.
	 * @return void
	 */
	function procDocumentMakeXmlFile()
	{
		// Check input values
		$module_srl = Context::get('module_srl');
		// Check permissions
		$columnList = array('module_srl', 'module');
		$module_info = ModuleModel::getModuleInfoByModuleSrl($module_srl, $columnList);
		$grant = ModuleModel::getGrant($module_info, Context::get('logged_info'));
		if(!$grant->manager) return new BaseObject(-1, 'msg_not_permitted');

		$xml_file = $this->makeCategoryFile($module_srl);
		// Set return value
		$this->add('xml_file',$xml_file);
	}

	/**
	 * Save the category in a cache file
	 * @param int $module_srl
	 * @return string
	 */
	function makeCategoryFile($module_srl)
	{
		// Return if there is no information you need for creating a cache file
		$module_srl = intval($module_srl);
		if(!$module_srl) return false;
		// Get module information (to obtain mid)
		$mid = ModuleModel::getMidByModuleSrl($module_srl);

		if(!is_dir('./files/cache/document_category')) FileHandler::makeDir('./files/cache/document_category');
		// Cache file's name
		$xml_file = sprintf("./files/cache/document_category/%d.xml.php", $module_srl);
		$php_file = sprintf("./files/cache/document_category/%d.php", $module_srl);
		// Get a category list
		$args = new stdClass();
		$args->module_srl = $module_srl;
		$args->sort_index = 'list_order';
		$output = executeQueryArray('document.getCategoryList', $args);

		$category_list = $output->data;

		if(!is_array($category_list)) $category_list = array($category_list);

		$category_count = count($category_list);
		for($i=0;$i<$category_count;$i++)
		{
			$category_srl = $category_list[$i]->category_srl;
			if(!preg_match('/^[0-9,]+$/', $category_list[$i]->group_srls)) $category_list[$i]->group_srls = '';
			$list[$category_srl] = $category_list[$i];
		}
		// Create the xml file without node data if no data is obtained
		if(!isset($list) || !$list)
		{
			$xml_buff = "<root />";
			FileHandler::writeFile($xml_file, $xml_buff);
			FileHandler::writeFile($php_file, '<?php if(!defined("__XE__")) exit(); ?>');
			return $xml_file;
		}
		// Change to an array if only a single data is obtained
		if(!is_array($list)) $list = array($list);
		// Create a tree for loop
		foreach($list as $category_srl => $node)
		{
			$node->mid = $mid;
			$parent_srl = (int)$node->parent_srl;
			$tree[$parent_srl][$category_srl] = $node;
		}
		// A common header to set permissions and groups of the cache file
		$header_script =
			'$lang_type = Context::getLangType(); '.
			'$is_logged = Context::get(\'is_logged\'); '.
			'$logged_info = Context::get(\'logged_info\'); '.
			'if($is_logged) {'.
			'if($logged_info->is_admin=="Y") $is_admin = true; '.
			'else $is_admin = false; '.
			'$group_srls = array_keys($logged_info->group_list); '.
			'} else { '.
			'$is_admin = false; '.
			'$group_srls = array(); '.
			'} '."\n";

		// Create the xml cache file (a separate session is needed for xml cache)
		$xml_header_buff = '';
		$xml_body_buff = $this->getXmlTree($tree[0], $tree, 0, $xml_header_buff);
		$xml_buff = sprintf(
			'<?php '.
			'require_once(\''.FileHandler::getRealPath('./common/autoload.php').'\'); '.
			'Context::init(); '.
			'Context::setCacheControl(0); '.
			'header("Content-Type: text/xml; charset=UTF-8"); '.
			'%s'.
			'%s '.
			'Context::close();'.
			'?>'.
			'<root>%s</root>',
			$header_script,
			$xml_header_buff,
			$xml_body_buff
		);
		// Create php cache file
		$php_header_buff = '$_titles = array();';
		$php_header_buff .= '$_descriptions = array();';
		$php_output = $this->getPhpCacheCode($tree[0], $tree, 0, $php_header_buff);
		$php_buff = sprintf(
			'<?php '.
			'if(!defined("__XE__")) exit(); '.
			'%s'.
			'%s'.
			'$menu = new stdClass;'.
			'$menu->list = array(%s); ',
			$header_script,
			$php_header_buff,
			$php_output['buff']
		);
		// Save File
		FileHandler::writeFile($xml_file, $xml_buff);
		FileHandler::writeFile($php_file, $php_buff);
		return $xml_file;
	}

	/**
	 * Create the xml data recursively referring to parent_srl
	 * In the menu xml file, node tag is nested and xml doc enables the admin page to have a menu
	 * (tree menu is implemented by reading xml file from the tree_menu.js)
	 * @param array $source_node
	 * @param array $tree
	 * @param int $site_srl
	 * @param string $xml_header_buff
	 * @return string
	 */
	function getXmlTree($source_node, $tree, $site_srl, &$xml_header_buff)
	{
		if(!$source_node) return;

		$buff = "";
		foreach($source_node as $category_srl => $node)
		{
			$child_buff = "";
			// Get data of the child nodes
			if($category_srl && isset($tree[$category_srl]) && $tree[$category_srl])
			{
				$child_buff = $this->getXmlTree($tree[$category_srl], $tree, $site_srl, $xml_header_buff);
			}
			// List variables
			$expand = isset($node->expand) ? $node->expand : 'N';
			$is_default = isset($node->is_default) ? $node->is_default : 'N';
			$group_srls = ($node->group_srls) ? $node->group_srls : '';
			$mid = ($node->mid) ? $node->mid : '';
			$module_srl = ($node->module_srl) ? $node->parent_srl : '';
			$parent_srl = ($node->parent_srl) ? $node->parent_srl : '';
			$color = ($node->color) ? $node->color : '';
			$description = ($node->description) ? $node->description : '';
			// If node->group_srls value exists
			if($group_srls) $group_check_code = sprintf('($is_admin==true||(is_array($group_srls)&&count(array_intersect($group_srls, array(%s)))))',$group_srls);
			else $group_check_code = "true";

			$title = $node->title;
			$oModuleAdminModel = getAdminModel('module');

			$langs = $oModuleAdminModel->getLangCode($site_srl, $title);
			if(count($langs))
			{
				foreach($langs as $key => $val)
				{
					$xml_header_buff .= sprintf('$_titles[%d][%s] = %s; ', $category_srl, var_export($key, true), var_export(escape($val, false), true));
				}
			}

			$langx = $oModuleAdminModel->getLangCode($site_srl, $description);
			if(count($langx))
			{
				foreach($langx as $key => $val)
				{
					$xml_header_buff .= sprintf('$_descriptions[%d][%s] = %s; ', $category_srl, var_export($key, true), var_export(escape($val, false), true));
				}
			}

			$attribute = sprintf(
				'mid="%s" module_srl="%d" node_srl="%d" parent_srl="%d" category_srl="%d" text="<?php echo (%s?($_titles[%d][$lang_type]):"")?>" url=%s expand=%s is_default=%s color=%s description="<?php echo (%s?($_descriptions[%d][$lang_type]):"")?>" document_count="%d" ',
				$mid,
				$module_srl,
				$category_srl,
				$parent_srl,
				$category_srl,
				$group_check_code,
				$category_srl,
				str_replace("'", '"', var_export(getUrl('','mid',$node->mid,'category',$category_srl), true)),
				str_replace("'", '"', var_export($expand, true)),
				str_replace("'", '"', var_export($is_default, true)),
				str_replace("'", '"', var_export(escape($color, false), true)),
				$group_check_code,
				$category_srl,
				$node->document_count
			);

			if($child_buff) $buff .= sprintf('<node %s>%s</node>', $attribute, $child_buff);
			else $buff .= sprintf('<node %s />', $attribute);
		}
		return $buff;
	}

	/**
	 * Change sorted nodes in an array to the php code and then return
	 * When using menu on tpl, you can directly xml data. howver you may need javascrips additionally.
	 * Therefore, you can configure the menu info directly from php cache file, not through DB.
	 * You may include the cache in the ModuleHandler::displayContent()
	 * @param array $source_node
	 * @param array $tree
	 * @param int $site_srl
	 * @param string $php_header_buff
	 * @return array
	 */
	function getPhpCacheCode($source_node, $tree, $site_srl, &$php_header_buff)
	{
		$output = array("buff"=>"", "category_srl_list"=>array());
		if(!$source_node) return $output;

		// Set to an arraty for looping and then generate php script codes to be included
		foreach($source_node as $category_srl => $node)
		{
			// Get data from child nodes first if exist.
			if($category_srl && isset($tree[$category_srl]) && $tree[$category_srl])
			{
				$child_output = $this->getPhpCacheCode($tree[$category_srl], $tree, $site_srl, $php_header_buff);
			}
			else
			{
				$child_output = array("buff"=>"", "category_srl_list"=>array());
			}

			// Set values into category_srl_list arrary if url of the current node is not empty
			$child_output['category_srl_list'][] = $node->category_srl;
			$output['category_srl_list'] = array_merge($output['category_srl_list'], $child_output['category_srl_list']);

			// If node->group_srls value exists
			if($node->group_srls) {
				$group_check_code = sprintf('($is_admin==true||(is_array($group_srls)&&count(array_intersect($group_srls, array(%s)))))',$node->group_srls);
			} else {
				$group_check_code = "true";
			}

			// List variables
			$selected = '"' . implode('","', $child_output['category_srl_list']) . '"';
			$child_buff = $child_output['buff'];
			$expand = $node->expand ?? 'N';
			$is_default = $node->is_default ?? 'N';

			$title = $node->title;
			$description = $node->description;
			$oModuleAdminModel = getAdminModel('module');
			$langs = $oModuleAdminModel->getLangCode($site_srl, $title);

			if(count($langs))
			{
				foreach($langs as $key => $val)
				{
					$val = htmlspecialchars($val, ENT_COMPAT | ENT_HTML401, 'UTF-8', false);
					$php_header_buff .= sprintf(
						'$_titles[%d][%s] = %s; ',
						$category_srl,
						var_export($key, true),
						var_export($val, true)
					);
				}
			}

			$langx = $oModuleAdminModel->getLangCode($site_srl, $description);

			if(count($langx))
			{
				foreach($langx as $key => $val)
				{
					$val = htmlspecialchars($val, ENT_COMPAT | ENT_HTML401, 'UTF-8', false);
					$php_header_buff .= sprintf(
						'$_descriptions[%d][%s] = %s; ',
						$category_srl,
						var_export($key, true),
						var_export($val, true)
					);
				}
			}

			// Create attributes(Use the category_srl_list to check whether to belong to the menu's node. It seems to be tricky but fast fast and powerful;)
			$attribute = sprintf(
				'"mid" => "%s", "module_srl" => "%d","node_srl"=>"%d","category_srl"=>"%d","parent_srl"=>"%d","text"=>$_titles[%d][$lang_type],"selected"=>(in_array(Context::get("category"),array(%s))?1:0),"expand"=>%s,"is_default"=>%s,"color"=>%s,"description"=>$_descriptions[%d][$lang_type],"list"=>array(%s),"document_count"=>"%d","grant"=>%s?true:false',
				$node->mid,
				$node->module_srl,
				$node->category_srl,
				$node->category_srl,
				$node->parent_srl,
				$node->category_srl,
				$selected,
				var_export($expand, true),
				var_export($is_default, true),
				var_export($node->color, true),
				$node->category_srl,
				$child_buff,
				$node->document_count,
				$group_check_code
			);

			// Generate buff data
			$output['buff'] .=  sprintf('%s=>array(%s),', $node->category_srl, $attribute);
		}

		return $output;
	}

	/**
	 * A method to add a pop-up menu which appears when clicking
	 * @param string $url
	 * @param string $str
	 * @param string $icon
	 * @param string $target
	 * @return void
	 */
	function addDocumentPopupMenu($url, $str, $icon = '', $target = '_blank')
	{
		$document_popup_menu_list = Context::get('document_popup_menu_list');
		if(!is_array($document_popup_menu_list)) $document_popup_menu_list = array();

		$obj = new stdClass();
		$obj->url = $url;
		$obj->str = $str;
		$obj->icon = $icon;
		$obj->target = $target;
		$document_popup_menu_list[] = $obj;

		Context::set('document_popup_menu_list', $document_popup_menu_list);
	}

	/**
	 * Saved in the session when an administrator selects a post
	 * @return void|Object
	 */
	function procDocumentAddCart()
	{
		if(!Context::get('is_logged'))
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}

		// Get document_srl
		$srls = Context::get('srls');
		if (is_array($srls))
		{
			$document_srls = array_map('intval', $srls);
		}
		else
		{
			$document_srls = array_map('intval', explode(',', $srls));
		}

		$document_srls = array_unique(array_filter($document_srls, function($srl) {
			return $srl > 0;
		}));
		if (!count($document_srls))
		{
			return;
		}

		// Get module_srl of the documents
		$args = new stdClass;
		$args->list_count = count($document_srls);
		$args->document_srls = implode(',',$document_srls);
		$args->order_type = 'asc';
		$output = executeQueryArray('document.getDocuments', $args);
		if(!$output->data) return new BaseObject();

		unset($document_srls);
		foreach($output->data as $key => $val)
		{
			$document_srls[$val->module_srl][] = $val->document_srl;
		}
		if(!$document_srls || !count($document_srls)) return new BaseObject();

		// Check if each of module administrators exists. Top-level administator will have a permission to modify every document of all modules.(Even to modify temporarily saved or trashed documents)
		$module_srls = array_keys($document_srls);
		for($i=0;$i<count($module_srls);$i++)
		{
			$module_srl = $module_srls[$i];
			$module_info = ModuleModel::getModuleInfoByModuleSrl($module_srl);
			$logged_info = Context::get('logged_info');
			if($logged_info->is_admin != 'Y')
			{
				if(!$module_info)
				{
					unset($document_srls[$module_srl]);
					continue;
				}
				$grant = ModuleModel::getGrant($module_info, $logged_info);
				if(!$grant->manager)
				{
					unset($document_srls[$module_srl]);
					continue;
				}
			}
		}
		if(!count($document_srls)) return new BaseObject();

		foreach($document_srls as $module_srl => $documents)
		{
			$cnt = count($documents);
			for($i=0;$i<$cnt;$i++)
			{
				$document_srl = (int)trim($documents[$i]);
				if(!$document_srls) continue;
				if($_SESSION['document_management'][$document_srl]) unset($_SESSION['document_management'][$document_srl]);
				else $_SESSION['document_management'][$document_srl] = true;
			}
		}
	}

	/**
	 * Move/ Delete the document in the seession
	 * @return void|Object
	 */
	function procDocumentManageCheckedDocument()
	{
		@set_time_limit(0);
		$logged_info = Context::get('logged_info');

		$obj = new stdClass;
		$obj->type = Context::get('type');
		$obj->document_list = array();
		$obj->document_srl_list = array();
		$obj->target_module_srl = intval(Context::get('module_srl') ?: Context::get('target_module_srl'));
		$obj->target_category_srl = intval(Context::get('target_category_srl'));
		$obj->manager_message = Context::get('message_content') ? nl2br(escape(strip_tags(Context::get('message_content')))) : '';
		$obj->send_message = $obj->manager_message || Context::get('send_default_message') == 'Y';
		$obj->return_message = '';

		// Check permission of target module
		if($obj->target_module_srl)
		{
			$module_info = ModuleModel::getModuleInfoByModuleSrl($obj->target_module_srl);
			if (!$module_info->module_srl)
			{
				throw new Rhymix\Framework\Exceptions\InvalidRequest;
			}
			$module_grant = ModuleModel::getGrant($module_info, $logged_info);
			if (!$module_grant->manager)
			{
				throw new Rhymix\Framework\Exceptions\NotPermitted;
			}
		}

		// Set Cart
		$cart = Context::get('cart');
		if(!is_array($cart))
		{
			$cart = explode('|@|', $cart);
		}
		$obj->document_srl_list = array_unique(array_map('intval', $cart));

		// Set document list
		$obj->document_list = DocumentModel::getDocuments($obj->document_srl_list, false, false);
		if(empty($obj->document_list))
		{
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}

		// Call a trigger (before)
		$output = ModuleHandler::triggerCall('document.manage', 'before', $obj);
		if(!$output->toBool())
		{
			return $output;
		}

		$oController = DocumentAdminController::getInstance();
		if($obj->type == 'move')
		{
			if(!$obj->target_module_srl)
			{
				throw new Rhymix\Framework\Exception('fail_to_move');
			}

			$output = $oController->moveDocumentModule($obj->document_srl_list, $obj->target_module_srl, $obj->target_category_srl);
			if(!$output->toBool())
			{
				return $output;
			}

			$obj->return_message = 'success_moved';
		}
		else if($obj->type == 'copy')
		{
			if(!$obj->target_module_srl)
			{
				throw new Rhymix\Framework\Exception('fail_to_move');
			}

			$output = $oController->copyDocumentModule($obj->document_srl_list, $obj->target_module_srl, $obj->target_category_srl);
			if(!$output->toBool())
			{
				return $output;
			}

			$obj->return_message = 'success_copied';
		}
		else if($obj->type == 'delete')
		{
			foreach ($obj->document_list as $document_srl => $oDocument)
			{
				$output = $this->deleteDocument($document_srl, true);
				if(!$output->toBool())
				{
					unset($obj->document_list[$document_srl]);
					$obj->return_message = $output->getMessage();
				}
			}

			$obj->return_message = $obj->return_message ?: 'success_deleted';
		}
		else if($obj->type == 'trash')
		{
			$args = new stdClass;
			$args->description = $obj->manager_message;

			foreach ($obj->document_list as $document_srl => $oDocument)
			{
				$args->document_srl = $document_srl;
				$output = $this->moveDocumentToTrash($args);
				if(!$output->toBool())
				{
					unset($obj->document_list[$document_srl]);
					$obj->return_message = $output->getMessage();
				}
			}

			$obj->return_message = $obj->return_message ?: 'success_trashed';
		}
		else if($obj->type == 'cancelDeclare')
		{
			$args = new stdClass;
			$args->document_srl = $obj->document_srl_list;
			$output = executeQuery('document.deleteDeclaredDocuments', $args);
			if(!$output->toBool())
			{
				return $output;
			}
			if(Context::get('prevent_redeclare') !== 'Y')
			{
				$output = executeQuery('document.deleteDocumentDeclaredLog', $args);
				if(!$output->toBool())
				{
					return $output;
				}
			}
			$obj->return_message = 'success_declare_canceled';
		}
		else
		{
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}

		// Call a trigger (after)
		ModuleHandler::triggerCall('document.manage', 'after', $obj);

		// Send a message
		$actions = lang('default_message_verbs');
		if(isset($actions[$obj->type]) && $obj->send_message)
		{
			// Set message
			$title = sprintf(lang('default_message_format'), $actions[$obj->type]);
			$content = <<< Content
<div style="padding:10px 0;"><strong>{$title}</strong></div>
<p>{$obj->manager_message}</p>
<hr>
<ul>%1\$s</ul>
Content;
			$document_item = '<li><a href="%1$s">%2$s</a></li>';

			// Set recipient
			$recipients = array();
			foreach ($obj->document_list as $document_srl => $oDocument)
			{
				if(!($member_srl = abs($oDocument->get('member_srl'))) || $logged_info->member_srl == $member_srl)
				{
					continue;
				}

				if(!isset($recipients[$member_srl]))
				{
					$recipients[$member_srl] = array();
				}

				$recipients[$member_srl][] = sprintf($document_item, $oDocument->getPermanentUrl(), $oDocument->getTitleText());
			}

			// Send
			$oCommunicationController = CommunicationController::getInstance();
			foreach ($recipients as $member_srl => $items)
			{
				$oCommunicationController->sendMessage($this->user->member_srl, $member_srl, $title, sprintf($content, implode('', $items)), true, null, false);
			}
		}

		$_SESSION['document_management'] = array();

		$this->setMessage($obj->return_message);
		$this->setRedirectUrl(Context::get('success_return_url') ?: getNotEncodedUrl('', 'module', 'admin', 'act', 'dispDocumentAdminList'));
	}

	/**
	 * Insert document module config
	 * @return void
	 */
	function procDocumentInsertModuleConfig()
	{
		$target_module_srl = Context::get('target_module_srl');
		$target_module_srl = array_map('trim', explode(',', $target_module_srl));
		$logged_info = Context::get('logged_info');
		$module_srl = array();
		foreach ($target_module_srl as $srl)
		{
			if (!$srl) continue;

			$module_info = ModuleModel::getModuleInfoByModuleSrl($srl);
			if (!$module_info->module_srl)
			{
				throw new Rhymix\Framework\Exceptions\InvalidRequest;
			}

			$module_grant = ModuleModel::getGrant($module_info, $logged_info);
			if (!$module_grant->manager)
			{
				throw new Rhymix\Framework\Exceptions\NotPermitted;
			}

			$module_srl[] = $srl;
		}

		$document_config = new stdClass();
		$document_config->use_history = Context::get('use_history');
		if(!$document_config->use_history) $document_config->use_history = 'N';

		$document_config->use_vote_up = Context::get('use_vote_up');
		if(!$document_config->use_vote_up) $document_config->use_vote_up = 'Y';

		$document_config->use_vote_down = Context::get('use_vote_down');
		if(!$document_config->use_vote_down) $document_config->use_vote_down = 'Y';

		$document_config->allow_vote_from_same_ip = Context::get('allow_vote_from_same_ip');
		if(!$document_config->allow_vote_from_same_ip) $document_config->allow_vote_from_same_ip = 'N';

		$document_config->allow_vote_cancel = Context::get('allow_vote_cancel');
		if(!$document_config->allow_vote_cancel) $document_config->allow_vote_cancel = 'N';

		$document_config->allow_vote_non_member = Context::get('allow_vote_non_member');
		if(!$document_config->allow_vote_non_member) $document_config->allow_vote_non_member = 'N';

		$document_config->allow_declare_from_same_ip = Context::get('allow_declare_from_same_ip');
		if(!$document_config->allow_declare_from_same_ip) $document_config->allow_declare_from_same_ip = 'N';

		$document_config->allow_declare_cancel = Context::get('allow_declare_cancel');
		if(!$document_config->allow_declare_cancel) $document_config->allow_declare_cancel = 'N';

		$document_config->declared_message = Context::get('declared_message');
		if(!is_array($document_config->declared_message)) $document_config->declared_message = array();
		$document_config->declared_message = array_values($document_config->declared_message);

		$oModuleController = ModuleController::getInstance();
		foreach ($module_srl as $srl)
		{
			$output = $oModuleController->insertModulePartConfig('document',$srl,$document_config);
		}

		$this->setError(-1);
		$this->setMessage('success_updated', 'info');

		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispBoardAdminContent');
		$this->setRedirectUrl($returnUrl);
	}

	/**
	 * Document temporary save
	 * @return void|Object
	 */
	function procDocumentTempSave()
	{
		if(!$this->module_srl)
		{
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}

		$obj = Context::getRequestVars();
		$obj->module_srl = $this->module_srl;
		$obj->status = $this->getConfigStatus('temp');
		$obj->list_order = $obj->update_order = 0;
		unset($obj->extra_vars);

		// unset document style if not manager
		if(!$this->grant->manager)
		{
			unset($obj->is_notice);
			unset($obj->title_color);
			unset($obj->title_bold);
		}

		$oDocument = DocumentModel::getDocument($obj->document_srl);

		// Update if already exists
		if($oDocument->isExists())
		{
			if(!$oDocument->isGranted())
			{
				throw new Rhymix\Framework\Exceptions\NotPermitted;
			}

			if($oDocument->get('status') != $this->getConfigStatus('temp'))
			{
				throw new Rhymix\Framework\Exceptions\InvalidRequest;
			}

			$output = $this->updateDocument($oDocument, $obj);
		}
		// Otherwise, get a new
		else
		{
			$output = $this->insertDocument($obj);

			$oDocument = DocumentModel::getDocument($output->get('document_srl'));
		}

		// Return error if save failed for any reason
		if (!$output->toBool())
		{
			return $output;
		}

		// Set the attachment to be invalid state
		if($oDocument->hasUploadedFiles())
		{
			$args = new stdClass;
			$args->isvalid = 'N';
			$args->upload_target_srl = $oDocument->document_srl;
			executeQuery('file.updateFileValid', $args);
		}

		$this->setMessage('success_saved');
		$this->add('document_srl', $output->get('document_srl'));
	}

	/**
	 * Return Document List for exec_xml
	 * @return void|Object
	 */
	function procDocumentGetList()
	{
		if(!Context::get('is_logged'))
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}

		$documentSrls = Context::get('document_srls');
		if($documentSrls) $documentSrlList = explode(',', $documentSrls);

		if(count($documentSrlList) > 0)
		{
			$columnList = array('document_srl', 'title', 'nick_name', 'status');
			$documentList = DocumentModel::getDocuments($documentSrlList, $this->grant->is_admin, false, $columnList);
		}
		else
		{
			$documentList = array();
			$this->setMessage(lang('no_documents'));
		}
		$oSecurity = new Security($documentList);
		$oSecurity->encodeHTML('..variables.');
		$this->add('document_list', array_values($documentList));
	}

	/**
	 * Clear document cache
	 *
	 * @param int $document_srl
	 * @param string $type
	 */
	public static function clearDocumentCache($document_srl, $type = 'all')
	{
		if ($type === 'all')
		{
			Rhymix\Framework\Cache::delete('document_item:' . getNumberingPath($document_srl) . $document_srl);
			Rhymix\Framework\Cache::delete('seo:document_images:' . $document_srl);
			Rhymix\Framework\Cache::delete('site_and_module:document_srl:' . $document_srl);
			unset($GLOBALS['XE_DOCUMENT_LIST'][$document_srl]);
		}
		if ($type === 'all' || $type === 'extra_vars')
		{
			unset($GLOBALS['XE_EXTRA_VARS'][$document_srl]);
			unset($GLOBALS['XE_EXTRA_CHK'][$document_srl]);
			unset($GLOBALS['RX_DOCUMENT_LANG'][$document_srl]);
		}
	}

	/**
	 * For old version, comment allow status check.
	 * @param object $obj
	 * @return void
	 */
	function _checkCommentStatusForOldVersion(&$obj)
	{
		if(!isset($obj->allow_comment)) $obj->allow_comment = 'N';
		if(!isset($obj->lock_comment)) $obj->lock_comment = 'N';

		if($obj->allow_comment == 'Y' && $obj->lock_comment == 'N') $obj->commentStatus = 'ALLOW';
		else $obj->commentStatus = 'DENY';
	}

	/**
	 * For old version, document status check.
	 * @param object $obj
	 * @return void
	 */
	function _checkDocumentStatusForOldVersion(&$obj)
	{
		if(!$obj->status)
		{
			if($obj->is_secret == 'Y')
			{
				$obj->status = $this->getConfigStatus('secret');
			}
			else
			{
				$obj->status = $this->getConfigStatus('public');
			}
		}
	}

	/**
	 * A typo of updateUploadedCount, maintained for backward compatibility.
	 *
	 * @deprecated
	 */
	public function updateUploaedCount($document_srl_list)
	{
		return $this->updateUploadedCount($document_srl_list);
	}

	public function updateUploadedCount($document_srl_list)
	{
		if(!is_array($document_srl_list))
		{
			$document_srl_list = array($document_srl_list);
		}

		if(empty($document_srl_list))
		{
			return;
		}

		$document_srl_list = array_unique($document_srl_list);

		foreach($document_srl_list as $document_srl)
		{
			$document_srl = (int)$document_srl;
			if ($document_srl <= 0)
			{
				continue;
			}

			$args = new stdClass;
			$args->document_srl = $document_srl;
			$args->uploaded_count = FileModel::getFilesCount($document_srl, 'doc');
			executeQuery('document.updateUploadedCount', $args);
		}
	}

	function triggerAfterDeleteFile($file)
	{
		$oDocument = DocumentModel::getDocument($file->upload_target_srl, false, false);
		if(!$oDocument->isExists())
		{
			return;
		}

		$this->updateUploadedCount($file->upload_target_srl);
	}

	/**
	 * Copy extra keys when module copied
	 * @param object $obj
	 * @return void
	 */
	function triggerCopyModuleExtraKeys(&$obj)
	{
		$documentExtraKeys = DocumentModel::getExtraKeys($obj->originModuleSrl);

		if(is_array($documentExtraKeys) && is_array($obj->moduleSrlList))
		{
			foreach($obj->moduleSrlList AS $key=>$value)
			{
				foreach($documentExtraKeys AS $extraItem)
				{
					$this->insertDocumentExtraKey($value, $extraItem->idx, $extraItem->name, $extraItem->type, $extraItem->is_required , $extraItem->search , $extraItem->default , $extraItem->desc, $extraItem->eid, $extraItem->is_strict, $extraItem->options);
				}
			}
		}
	}

	function triggerCopyModule(&$obj)
	{
		$documentConfig = ModuleModel::getModulePartConfig('document', $obj->originModuleSrl);

		$oModuleController = ModuleController::getInstance();
		if(is_array($obj->moduleSrlList))
		{
			foreach($obj->moduleSrlList AS $key=>$moduleSrl)
			{
				$oModuleController->insertModulePartConfig('document', $moduleSrl, $documentConfig);
			}
		}
	}
}
/* End of file document.controller.php */
/* Location: ./modules/document/document.controller.php */
