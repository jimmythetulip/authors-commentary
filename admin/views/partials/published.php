<?php

/**
 * Provides the 'Published' view for the corresponding tab in the Post Meta Box.
 *
 * @link       http://code.tutsplus.com/tutorials/creating-maintainable-wordpress-meta-boxes-the-front-end--cms-22383
 * @since      0.3.0
 *
 * @package    Authors_Commentary
 * @subpackage Authors_Commentary/admin/partials
 */
?>

<div class="inside hidden">
	<?php $comments = $this->load_post_comments(); ?>
	<ul id="author-commentary-comments">
		<?php foreach ( $comments as $comment ) { ?>
			<li>
				<label for="authors-commentary-comment-<?php echo $comment->comment_ID ?>">
					<input type="checkbox" id="authors-commentary-comment-<?php echo $comment->comment_ID ?>" name="authors-commentary-comment-<?php echo $comment->comment_ID ?>" />
					This comment has received a reply.
				</label>
				<p>
					<em><?php echo $comment->comment_author; ?></em>:
					<?php echo $comment->comment_content; ?>
				</p>
				<hr />
			</li>
		<?php } ?>
	</ul>
</div>