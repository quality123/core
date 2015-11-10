<?php

namespace OCP\Comments;

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
	public function id();

	/**
	 * returns the parent ID of the comment
	 *
	 * @return string
	 */
	public function parentId();

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
	public function childrenCount();

	/**
	 * returns the message of the comment
	 *
	 * @return string
	 */
	public function message();

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
	public function verb();

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
	public function actorType();

	/**
	 * sets (overwrites) the actor type
	 *
	 * @param string $actorType
	 * @return IComment
	 */
	public function setActorType($actorType);

	/**
	 * returns the actor ID
	 *
	 * @return string
	 */
	public function actorId();

	/**
	 * sets (overwrites) the actor id
	 *
	 * @param string $actorId
	 * @return IComment
	 */
	public function setActorId($actorId);

	/**
	 * returns the unix timestamp of the comment
	 *
	 * @return int
	 */
	public function timestamp();

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
	public function latestChildTimestamp();

	/**
	 * returns the object type the comment is attached to
	 *
	 * @return string
	 */
	public function objectType();

	/**
	 * sets (overwrites) the object typed of the comment
	 *
	 * @param string $objectType
	 * @return IComment
	 */
	public function setObjectType($objectType);

	/**
	 * returns the object id the comment is attached to
	 *
	 * @return string
	 */
	public function objectId();

	/**
	 * sets (overwrites) the objectId of the comment
	 *
	 * @param string $objectId
	 * @return IComment
	 */
	public function setObjectId($objectId);

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

