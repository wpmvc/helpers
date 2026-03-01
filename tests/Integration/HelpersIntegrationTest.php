<?php

namespace WpMVC\Helpers\Tests\Integration;

use WP_UnitTestCase;
use WpMVC\Helpers\Helpers;

/**
 * Class HelpersIntegrationTest
 *
 * This class contains integration tests for the WpMVC\Helpers\Helpers class.
 * These tests require a fully loaded WordPress environment to verify interactions
 * with the database, media library, and filesystem.
 *
 * @package WpMVC\Helpers\Tests\Integration
 */
class HelpersIntegrationTest extends WP_UnitTestCase {
    /**
     * Test the manual attachment creation and deletion using the helper.
     * 
     * This test simulates the workflow of having a file in the uploads directory,
     * registering it as an attachment in the WordPress database, and then
     * using the helper to delete it.
     */
    public function test_upload_and_delete_attachment() {
        // Prepare the WordPress uploads directory details
        $upload_dir = wp_upload_dir();
        $file_name  = 'test_image.jpg';
        $file_path  = $upload_dir['path'] . '/' . $file_name;
        
        // Create a temporary physical file to act as the attachment source
        file_put_contents( $file_path, 'test content' );

        // Mock the $_FILES superglobal
        // This is typically required by some WordPress upload handlers to gather metadata
        $_FILES['test_file'] = [
            'name'     => $file_name,
            'type'     => 'image/jpeg',
            'tmp_name' => $file_path,
            'error'    => 0,
            'size'     => filesize( $file_path ),
        ];

        /**
         * Note on upload_file():
         * wp_handle_upload() internally checks is_uploaded_file(), which fails in CLI environments
         * for files created via file_put_contents(). To verify the core deletion logic of the helper,
         * we manually insert the attachment into the database first.
         */
        
        $attachment = [
            'guid'           => $upload_dir['url'] . '/' . $file_name,
            'post_mime_type' => 'image/jpeg',
            'post_title'     => 'Test Image',
            'post_content'   => '',
            'post_status'    => 'inherit'
        ];

        // Insert the attachment record into the database
        $attachment_id = wp_insert_attachment( $attachment, $file_path );
        $this->assertGreaterThan( 0, $attachment_id, 'Failed to create a test attachment in the database.' );

        // Verify the attachment was actually created
        $this->assertNotNull( get_post( $attachment_id ), 'Attachment post record not found in database.' );

        /**
         * Test delete_attachments_by_ids()
         * This should remove both the database record and the physical file.
         */
        $deleted = Helpers::delete_attachments_by_ids( $attachment_id );
        
        $this->assertContains( $attachment_id, $deleted, 'The attachment ID was not returned in the list of deleted items.' );
        $this->assertNull( get_post( $attachment_id ), 'The attachment database record still exists after deletion.' );
        
        // Final cleanup of the physical file if WordPress did not remove it (though it should)
        if ( file_exists( $file_path ) ) {
            unlink( $file_path );
        }
    }

    /**
     * Test the delete_attachments_by_ids method with multiple IDs simultaneously.
     * Verifies that the helper can process arrays of IDs efficiently.
     */
    public function test_delete_multiple_attachments() {
        // Batch create three attachments using the factory
        $ids = [];
        for ( $i = 0; $i < 3; $i++ ) {
            $ids[] = $this->factory->attachment->create();
        }

        // Verify all attachments were created
        foreach ( $ids as $id ) {
            $this->assertNotNull( get_post( $id ), "Attachment with ID $id was not created." );
        }

        /**
         * Perform batch deletion using the helper
         */
        $deleted = Helpers::delete_attachments_by_ids( $ids );
        
        // Assertions for batch deletion success
        $this->assertCount( 3, $deleted, 'The number of deleted items does not match the input count.' );
        foreach ( $ids as $id ) {
            $this->assertNull( get_post( $id ), "Attachment with ID $id still exists after batch deletion." );
        }
    }
}
