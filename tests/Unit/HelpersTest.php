<?php

namespace WpMVC\Helpers\Tests\Unit;

use WP_UnitTestCase;
use WpMVC\Helpers\Helpers;

/**
 * Class HelpersTest
 *
 * This class contains unit tests for the static methods in the WpMVC\Helpers\Helpers class.
 * These tests focus on pure PHP logic, array manipulations, and string handling.
 *
 * @package WpMVC\Helpers\Tests\Unit
 */
class HelpersTest extends WP_UnitTestCase {
    /**
     * Test the maybe_json_decode method with a valid JSON string.
     * Expects an associative array.
     */
    public function test_maybe_json_decode_valid() {
        $json     = '{"key":"value"}';
        $expected = ['key' => 'value'];
        $this->assertEquals( $expected, Helpers::maybe_json_decode( $json ) );
    }

    /**
     * Test the maybe_json_decode method with an invalid JSON string.
     * Expects the original string to be returned.
     */
    public function test_maybe_json_decode_invalid() {
        $invalid_json = '{key:"value"}';
        $this->assertEquals( $invalid_json, Helpers::maybe_json_decode( $invalid_json ) );
    }

    /**
     * Test the maybe_json_decode method with non-string values.
     * Expects the original value to be returned unchanged.
     */
    public function test_maybe_json_decode_non_string() {
        // Test with an array
        $array = ['key' => 'value'];
        $this->assertEquals( $array, Helpers::maybe_json_decode( $array ) );

        // Test with an integer
        $number = 123;
        $this->assertEquals( $number, Helpers::maybe_json_decode( $number ) );
    }

    /**
     * Test the is_one_level_array method with a flat array.
     * Expects true.
     */
    public function test_is_one_level_array_true() {
        // Flat indexed array
        $array = ['a', 'b', 'c'];
        $this->assertTrue( Helpers::is_one_level_array( $array ) );

        // Flat associative array
        $assoc_array = ['key1' => 'val1', 'key2' => 'val2'];
        $this->assertTrue( Helpers::is_one_level_array( $assoc_array ) );
    }

    /**
     * Test the is_one_level_array method with a multi-dimensional array.
     * Expects false.
     */
    public function test_is_one_level_array_false() {
        // Multi-dimensional indexed array
        $array = ['a', ['b', 'c']];
        $this->assertFalse( Helpers::is_one_level_array( $array ) );

        // Multi-dimensional associative array
        $assoc_array = ['key1' => 'val1', 'key2' => ['val2']];
        $this->assertFalse( Helpers::is_one_level_array( $assoc_array ) );
    }

    /**
     * Test the array_merge_deep method with nested arrays.
     * Verifies that nested keys are merged rather than overwritten.
     */
    public function test_array_merge_deep() {
        $array1 = [
            'key1' => 'val1',
            'key2' => [
                'nested1' => 'val2',
                'nested2' => 'val3',
            ],
        ];

        $array2 = [
            'key2' => [
                'nested2' => 'new_val3',
                'nested3' => 'val4',
            ],
            'key3' => 'val5',
        ];

        $expected = [
            'key1' => 'val1',
            'key2' => [
                'nested1' => 'val2',
                'nested2' => 'new_val3',
                'nested3' => 'val4',
            ],
            'key3' => 'val5',
        ];

        $this->assertEquals( $expected, Helpers::array_merge_deep( $array1, $array2 ) );
    }

    /**
     * Test the array_merge_deep method when overwriting scalars with arrays and vice versa.
     * Verifies that types are correctly swapped when they are not both arrays.
     */
    public function test_array_merge_deep_overwrite_types() {
        $array1 = [
            'key1' => 'val1',
            'key2' => ['nested' => 'val2'],
        ];

        $array2 = [
            'key1' => ['new_nested' => 'new_val'],
            'key2' => 'scalar_val',
        ];

        $expected = [
            'key1' => ['new_nested' => 'new_val'],
            'key2' => 'scalar_val',
        ];

        $this->assertEquals( $expected, Helpers::array_merge_deep( $array1, $array2 ) );
    }

    /**
     * Test the remove_null_values method.
     * Verifies that only NULL values are removed, and falsey values like 0, false, or empty string remain.
     */
    public function test_remove_null_values() {
        $array = [
            'key1' => 'val1',
            'key2' => null,
            'key3' => 0,
            'key4' => false,
            'key5' => '',
        ];

        $expected = [
            'key1' => 'val1',
            'key3' => 0,
            'key4' => false,
            'key5' => '',
        ];

        $this->assertEquals( $expected, Helpers::remove_null_values( $array ) );
    }

    /**
     * Test the get_user_ip_address method specifically using the HTTP_CLIENT_IP header.
     */
    public function test_get_user_ip_address_client_ip() {
        $_SERVER['HTTP_CLIENT_IP'] = '192.168.1.1';
        $this->assertEquals( '192.168.1.1', Helpers::get_user_ip_address() );
        unset( $_SERVER['HTTP_CLIENT_IP'] );
    }

    /**
     * Test the get_user_ip_address method specifically using the HTTP_X_FORWARDED_FOR header.
     * Verifies that the first valid IP from a comma-separated list is returned.
     */
    public function test_get_user_ip_address_forwarded_for() {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '192.168.1.2, 10.0.0.1';
        $this->assertEquals( '192.168.1.2', Helpers::get_user_ip_address() );
        unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
    }

    /**
     * Test the get_user_ip_address method using the standard REMOTE_ADDR header.
     */
    public function test_get_user_ip_address_remote_addr() {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.3';
        $this->assertEquals( '192.168.1.3', Helpers::get_user_ip_address() );
        unset( $_SERVER['REMOTE_ADDR'] );
    }

    /**
     * Test the get_user_ip_address method when no relevant server variables are set.
     * Expects null.
     */
    public function test_get_user_ip_address_no_ip() {
        // Clear IP related server flags
        unset( $_SERVER['HTTP_CLIENT_IP'] );
        unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
        unset( $_SERVER['REMOTE_ADDR'] );

        $this->assertNull( Helpers::get_user_ip_address() );
    }

    /**
     * Test the get_plugin_version method.
     * Creates a temporary plugin file to verify the regex parsing of the 'Version' header.
     */
    public function test_get_plugin_version() {
        // Create a fake plugin file in the WordPress plugins directory
        $plugin_dir = WP_PLUGIN_DIR . '/fake-plugin';
        if ( ! file_exists( $plugin_dir ) ) {
            mkdir( $plugin_dir, 0777, true );
        }

        $plugin_file = $plugin_dir . '/fake-plugin.php';
        file_put_contents( $plugin_file, "<?php\n/**\n * Plugin Name: Fake Plugin\n * Version: 1.2.3\n */" );

        $this->assertEquals( '1.2.3', Helpers::get_plugin_version( 'fake-plugin' ) );

        // Cleanup: remove temporary file and directory
        unlink( $plugin_file );
        rmdir( $plugin_dir );
    }

    /**
     * Test the get_plugin_version method when the plugin slug does not exist.
     * Expects null.
     */
    public function test_get_plugin_version_not_found() {
        $this->assertNull( Helpers::get_plugin_version( 'non-existent-plugin' ) );
    }

    /**
     * Test the request method.
     * Verifies that a WP_REST_Request object is created and correctly populated from $_GET and $_POST globals.
     */
    public function test_request_method() {
        $_GET['test_get']   = 'value_get';
        $_POST['test_post'] = 'value_post';

        $request = Helpers::request();

        // Check if the object is of correct type and has correctly mapped values
        $this->assertInstanceOf( \WP_REST_Request::class, $request );
        $this->assertEquals( 'value_get', $request->get_param( 'test_get' ) );
        $this->assertEquals( 'value_post', $request->get_param( 'test_post' ) );
    }
}
