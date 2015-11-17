<?php

namespace OCP\Comments;

/**
 * Interface IComment
 *
 * This class represents a comment and offers methods for modification.
 *
 * @package OCP\Comments
 * @since 9.0.0
 */
interface IComment {

	/**
	 * returns the ID of the comment
	 *
	 * It may return an empty string, if the comment was not stored.
	 * It is expected that the concrete Comment implementation gives an ID
	 * by itself (e.g. after saving).
	 *
	 * @return string
	 */
	public function getId();

	/**
	 * returns the parent ID of the comment
	 *
	 * @return string
	 */
	public function getParentId();

	/**
	 * sets the parent ID and returns itself
	 *
	 * @param string $parentId
	 * @return IComment
	 */
	public function setParentId($parentId);

	/**
	 * returns the number of children
	 *
	 * @return int
	 */
	public function getChildrenCount();

	/**
	 * returns the message of the comment
	 *
	 * @return string
	 */
	public function getMessage();

	/**
	 * sets the message of the comment and returns itself
	 *
	 * @param $message
	 * @return IComment
	 */
	public function setMessage($message);

	/**
	 * returns the verb of the comment
	 *
	 * @return string
	 */
	public function getVerb();

	/**
	 * sets the verb of the comment, e.g. 'comment' or 'like'
	 *
	 * @param $verb
	 * @return IComment
	 */
	public function setVerb($verb);

	/**
	 * returns the actor type
	 *
	 * @return string
	 */
	public function getActorType();

	/**
	 * returns the actor ID
	 *
	 * @return string
	 */
	public function getActorId();

	/**
	 * sets (overwrites) the actor type and id
	 *
	 * @param string $actorType e.g. 'user'
	 * @param string $actorId e.g. 'zombie234'
	 * @return IComment
	 */
	public function setActor($actorType, $actorId);

	/**
	 * returns the unix timestamp of the comment
	 *
	 * @return int
	 */
	public function getTimestamp();

	/**
	 * sets the timestamp of the comment and returns itself
	 *
	 * @param int $timestamp
	 * @return IComment
	 */
	public function setTimestamp($timestamp);

	/**
	 * returns the timestamp of the most recent child
	 *
	 * @return int
	 */
	public function getLatestChildTimestamp();

	/**
	 * returns the object type the comment is attached to
	 *
	 * @return string
	 */
	public function getObjectType();

	/**
	 * returns the object id the comment is attached to
	 *
	 * @return string
	 */
	public function getObjectId();

	/**
	 * sets (overwrites) the object of the comment
	 *
	 * @param string $objectType e.g. 'file'
	 * @param string $objectId e.g. '16435'
	 * @return IComment
	 */
	public function setObject($objectType, $objectId);

	/**
	 * saves the comment permanently and returns itself
	 *
	 * @return IComment
	 */
	public function save();

	/**
	 * permanently deletes the comment and returns itself
	 *
	 * @return IComment
	 */
	public function delete();

	/**
	 * loads its data from its storage and returns itself
	 *
	 * @return IComment
	 */
	public function load();

}

