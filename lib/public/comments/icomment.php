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
	 * @since 9.0.0
	 */
	public function getId();

	/**
	 * returns the parent ID of the comment
	 *
	 * @return string
	 * @since 9.0.0
	 */
	public function getParentId();

	/**
	 * sets the parent ID and returns itself
	 *
	 * @param string $parentId
	 * @return IComment
	 * @since 9.0.0
	 */
	public function setParentId($parentId);

	/**
	 * returns the number of children
	 *
	 * @return int
	 * @since 9.0.0
	 */
	public function getChildrenCount();

	/**
	 * returns the message of the comment
	 *
	 * @return string
	 * @since 9.0.0
	 */
	public function getMessage();

	/**
	 * sets the message of the comment and returns itself
	 *
	 * @param $message
	 * @return IComment
	 * @since 9.0.0
	 */
	public function setMessage($message);

	/**
	 * returns the verb of the comment
	 *
	 * @return string
	 * @since 9.0.0
	 */
	public function getVerb();

	/**
	 * sets the verb of the comment, e.g. 'comment' or 'like'
	 *
	 * @param $verb
	 * @return IComment
	 * @since 9.0.0
	 */
	public function setVerb($verb);

	/**
	 * returns the actor type
	 *
	 * @return string
	 * @since 9.0.0
	 */
	public function getActorType();

	/**
	 * returns the actor ID
	 *
	 * @return string
	 * @since 9.0.0
	 */
	public function getActorId();

	/**
	 * sets (overwrites) the actor type and id
	 *
	 * @param string $actorType e.g. 'user'
	 * @param string $actorId e.g. 'zombie234'
	 * @return IComment
	 * @since 9.0.0
	 */
	public function setActor($actorType, $actorId);

	/**
	 * returns the unix timestamp of the comment
	 *
	 * @return int
	 * @since 9.0.0
	 */
	public function getTimestamp();

	/**
	 * sets the timestamp of the comment and returns itself
	 *
	 * @param int $timestamp
	 * @return IComment
	 * @since 9.0.0
	 */
	public function setTimestamp($timestamp);

	/**
	 * returns the timestamp of the most recent child
	 *
	 * @return int
	 * @since 9.0.0
	 */
	public function getLatestChildTimestamp();

	/**
	 * returns the object type the comment is attached to
	 *
	 * @return string
	 * @since 9.0.0
	 */
	public function getObjectType();

	/**
	 * returns the object id the comment is attached to
	 *
	 * @return string
	 * @since 9.0.0
	 */
	public function getObjectId();

	/**
	 * sets (overwrites) the object of the comment
	 *
	 * @param string $objectType e.g. 'file'
	 * @param string $objectId e.g. '16435'
	 * @return IComment
	 * @since 9.0.0
	 */
	public function setObject($objectType, $objectId);

	/**
	 * saves the comment permanently and returns itself
	 *
	 * @return IComment
	 * @since 9.0.0
	 */
	public function save();

	/**
	 * permanently deletes the comment and returns itself
	 *
	 * @return IComment
	 * @since 9.0.0
	 */
	public function delete();

	/**
	 * loads its data from its storage and returns itself
	 *
	 * @return IComment
	 * @since 9.0.0
	 */
	public function load();

}

