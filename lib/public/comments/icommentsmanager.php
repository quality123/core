<?php

namespace OCP\Comments;

/**
 * Interface CommentsManager
 *
 * @package OCP\Comments
 * @since 9.0.0
 */
interface ICommentsManager {

	/**
	 * returns a comment instance
	 *
	 * @param string $id the ID of the comment
	 * @return IComment
	 * @throws NotFoundException
	 * @since 9.0.0
	 */
	public function get($id);

	/**
	 * returns the comment specified by the id and all it's child comments
	 *
	 * @param string $id
	 * @return []
	 * @since 9.0.0
	 *
	 * The return array looks like this
	 * [
	 * 	 'comment' => IComment, // root comment
	 *   'replies' =>
	 *   [
	 *     0 =>
	 *     [
	 *       'comment' => IComment,
	 *       'replies' =>
	 *       [
	 *         0 =>
	 *         [
	 *           'comment' => IComment,
	 *           'replies' => [ … ]
	 *         ],
	 *         …
	 *       ]
	 *     ]
	 *     1 =>
	 *     [
	 *       'comment' => IComment,
	 *       'replies'=> [ … ]
	 *     ],
	 *     …
	 *   ]
	 * ]
	 */
	public function getTree($id);

	/**
	 * returns comments for a specific object (e.g. a file).
	 *
	 * The sort order is always oldest to newest.
	 *
	 * @param string $objectType the object type, e.g. 'files'
	 * @param string $objectId the id of the object
	 * @param int $limit optional, number of maximum comments to be returned. if
	 * not specified, all comments are returned.
	 * @param int $offset optional, an unix timestamp of the oldest comments to
	 * be returned
	 * @return IComment[]
	 * @throws NotFoundException in case the requested type or id is not present
	 * @since 9.0.0
	 */
	public function getForObject($objectType, $objectId, $limit = 0, $offset = 0);

	/**
	 * @param $objectType string the object type, e.g. 'files'
	 * @param $objectId string the id of the object
	 * @return Int
	 * @throws NotFoundException in case the requested type or id is not present
	 * @since 9.0.0
	 */
	public function getNumberOfCommentsForObject($objectType, $objectId);

	/**
	 * creates a new comment
	 *
	 * @param string $actorType the actor type (e.g. 'user')
	 * @param string $actorId a user id
	 * @param string $objectType the object type the comment is attached to
	 * @param string $objectId the object id the comment is attached to
	 * @return IComment
	 * @since 9.0.0
	 */
	public function create($actorType, $actorId, $objectType, $objectId);

	/**
	 * removes references to specific actor (e.g. on user delete) of a comment.
	 * The comment itself must not get lost/deleted.
	 *
	 * @param string $actorType the actor type (e.g. 'user')
	 * @param string $actorId a user id
	 * @return boolean
	 * @since 9.0.0
	 */
	public function deleteReferencesOfActor($actorType, $actorId);

	/**
	 * deletes all comments made by a specific actor (e.g. on user delete)
	 *
	 * @param string $objectType the actor type (e.g. 'user')
	 * @param string $objectId a user id
	 * @return boolean
	 * @since 9.0.0
	 */
	public function deleteCommentsAtObject($objectType, $objectId);

}
