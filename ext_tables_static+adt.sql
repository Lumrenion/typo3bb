DROP VIEW IF EXISTS `view_tx_typo3bb_board_recursive_information`;
CREATE VIEW `view_tx_typo3bb_board_recursive_information` AS
  WITH RECURSIVE board_cte AS (
    SELECT board_start.uid, board_start.parent_board, board_start.read_permissions, board_start.write_permissions,
      board_start.hidden, board_start.deleted, board_start.tx_kesearch_index
      , board_start.uid as origin
    FROM tx_typo3bb_domain_model_board board_start
    WHERE board_start.hidden = 0 AND board_start.deleted = 0 -- exclude fields

    UNION ALL

    SELECT board_recursion.uid, board_recursion.parent_board, board_recursion.read_permissions, board_recursion.write_permissions,
      board_recursion.hidden, board_recursion.deleted, board_recursion.tx_kesearch_index
      , board_cte.origin
    FROM tx_typo3bb_domain_model_board board_recursion
      INNER JOIN board_cte ON board_cte.parent_board = board_recursion.uid
    #    WHERE board_recursion.hidden = 0 AND board_recursion.deleted = 0 -- do not exclude when resursion board is disabled, as it would falsify the permissions and the rootline
  )
  SELECT
    origin as uid,
    GROUP_CONCAT(read_permissions SEPARATOR '|') as read_permissions,
    GROUP_CONCAT(write_permissions SEPARATOR '|') as write_permissions,
    GROUP_CONCAT(board_cte.uid) as rootline,
    MAX(hidden) as tree_hidden, MAX(deleted) as tree_deleted, MIN(tx_kesearch_index) as tree_tx_kesearch_index
  FROM board_cte
  GROUP BY origin;

DROP VIEW IF EXISTS `view_tx_typo3bb_board_latest_post`;
CREATE VIEW `view_tx_typo3bb_board_latest_post` AS
  SELECT board.uid board, post.post_uid post
  FROM tx_typo3bb_domain_model_board board
    JOIN (
      SELECT MAX(latest_topic.post_uid) as post_uid, latest_topic.board
      FROM tx_typo3bb_domain_model_post post
        JOIN (
          SELECT
            MAX(post.uid) post_uid, topic.uid topic, topic.board
          FROM
            tx_typo3bb_domain_model_topic topic
            JOIN (
              SELECT post.*
              FROM
                tx_typo3bb_domain_model_post post
                JOIN (
                   SELECT topic, MAX(uid) AS uid
                   FROM tx_typo3bb_domain_model_post
                   WHERE tx_typo3bb_domain_model_post.deleted = 0 -- exclude fields
                   GROUP BY topic
                ) latest_post ON latest_post.topic = post.topic AND latest_post.uid = post.uid
            ) post ON topic.uid = post.topic
          WHERE topic.deleted = 0 -- exclude fields
          GROUP BY topic.uid
        ) latest_topic ON latest_topic.post_uid = post.uid
      WHERE post.deleted = 0 -- exclude fields
      GROUP BY latest_topic.board
    ) post ON post.board = board.uid
  WHERE
    board.hidden = 0 AND board.deleted = 0 -- exclude fields
  ORDER BY board, post;

DROP VIEW IF EXISTS `view_tx_typo3bb_board_children`;
CREATE VIEW `view_tx_typo3bb_board_children` AS
  WITH RECURSIVE board_cte AS (
    SELECT board_start.uid, board_start.parent_board, board_start.read_permissions, board_start.write_permissions
      , board_start.uid as origin
    FROM tx_typo3bb_domain_model_board board_start
    WHERE board_start.hidden = 0 AND board_start.deleted = 0 -- exclude fields

    UNION ALL

    SELECT board_recursion.uid, board_recursion.parent_board, board_recursion.read_permissions, board_recursion.write_permissions
      , board_cte.origin
    FROM tx_typo3bb_domain_model_board board_recursion
      INNER JOIN board_cte ON board_cte.uid = board_recursion.parent_board
    WHERE board_recursion.hidden = 0 AND board_recursion.deleted = 0 -- exclude fields
  )
  SELECT
    origin as uid,
    GROUP_CONCAT(board_cte.uid) as children
  FROM board_cte
  GROUP BY origin;

DELIMITER //
-- Method checks if a csv has any value in another csv. With | as separator in andHaystack, multiple csv can be AND stacked
-- 1,2|3,4 == (1 OR 2) AND (3 OR 4)
DROP FUNCTION IF EXISTS `findMultipleInAndSet` //
CREATE FUNCTION `findMultipleInAndSet` (needles TEXT, andHaystack TEXT) RETURNS BOOLEAN
DETERMINISTIC
  BEGIN
    DECLARE singleStack TEXT DEFAULT NULL;
    DECLARE singleStackLen INT DEFAULT NULL;
    DECLARE result INT DEFAULT 0;
    SET needles = REPLACE(needles, ',', '|');
    iterator:
    LOOP
      IF LENGTH(TRIM(andHaystack)) = 0 OR andHaystack IS NULL THEN LEAVE iterator; END IF;

      SET singleStack = SUBSTRING_INDEX(andHaystack, '|', 1);
      SET singleStackLen = LENGTH(singleStack);

      SELECT CONCAT(",", singleStack, ",") REGEXP CONCAT(",(", needles, "),") INTO result;
      IF (result = 0) THEN RETURN FALSE; END IF;

      SET andHaystack = INSERT(andHaystack, 1, singleStackLen + 1, '');
    END LOOP;

    RETURN TRUE;
  END //
-- Function hasAccess checks recursively if a usergroups csv has access to a board
DROP FUNCTION IF EXISTS `hasAccess` //
CREATE FUNCTION `hasAccess`(board INT, usergroups TEXT) RETURNS BOOLEAN
DETERMINISTIC
  BEGIN
    DECLARE readPermissions TEXT DEFAULT NULL;
    SET readPermissions = (SELECT read_permissions FROM view_tx_typo3bb_board_recursive_information WHERE uid = board);

    RETURN findMultipleInAndSet(usergroups, readPermissions);
  END //
DELIMITER ;