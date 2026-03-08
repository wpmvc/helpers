<?php

namespace WpMVC\Helpers\Tests\Unit;

use WP_UnitTestCase;
use WpMVC\Helpers\Date;
use DateTimeZone;
use Exception;

/**
 * Class DateTest
 *
 * This class contains unit tests for the WpMVC\Helpers\Date class.
 *
 * @package WpMVC\Helpers\Tests\Unit
 */
class DateTest extends WP_UnitTestCase {
    /**
     * Test the constructor with default values.
     */
    public function test_constructor_defaults() {
        $now  = time();
        $date = new Date();
        
        // Assert timestamp is approximately now (within 2 seconds to avoid race conditions)
        $this->assertLessThanOrEqual( 2, abs( $date->getTimestamp() - $now ) );
        
        // Assert default timezone is wp_timezone()
        $this->assertEquals( wp_timezone()->getName(), $date->getTimezone()->getName() );
    }

    /**
     * Test the now() static method.
     */
    public function test_now() {
        $date = Date::now();
        $this->assertInstanceOf( Date::class, $date );
        $this->assertLessThanOrEqual( 2, abs( $date->getTimestamp() - time() ) );
    }

    /**
     * Test the unit() method for accurate date math.
     */
    public function test_unit_accurate_math() {
        // Test February in a non-leap year
        $date = new Date( '2023-02-01' );
        $date->unit( 'month', 1 );
        $this->assertEquals( '2023-03-01', $date->format( 'Y-m-d' ) );

        // Test February in a leap year
        $date = new Date( '2024-02-01' );
        $date->unit( 'month', 1 );
        // Test year with leap day
        $date = new Date( '2024-02-29' );
        $date->unit( 'year', 1 );
        $this->assertEquals( '2025-03-01', $date->format( 'Y-m-d' ) );

        // Test subtraction
        $date = new Date( '2023-03-01' );
        $date->unit( 'month', 1, 'sub' );
        $this->assertEquals( '2023-02-01', $date->format( 'Y-m-d' ) );
    }

    /**
     * Test arithmetic wrappers.
     */
    public function test_arithmetic_wrappers() {
        $date = new Date( '2023-01-01' );
        
        $date->add_days( 5 );
        $this->assertEquals( '2023-01-06', $date->format( 'Y-m-d' ) );
        
        $date->sub_days( 2 );
        $this->assertEquals( '2023-01-04', $date->format( 'Y-m-d' ) );
        
        $date->add_months( 2 );
        $this->assertEquals( '2023-03-04', $date->format( 'Y-m-d' ) );
        
        $date->sub_months( 1 );
        $this->assertEquals( '2023-02-04', $date->format( 'Y-m-d' ) );
        
        $date->add_years( 1 );
        $this->assertEquals( '2024-02-04', $date->format( 'Y-m-d' ) );
        
        $date->sub_years( 2 );
        $this->assertEquals( '2022-02-04', $date->format( 'Y-m-d' ) );
    }

    /**
     * Test string representation.
     */
    public function test_string_representation() {
        $date   = new Date( '2023-05-15 10:30:00', new DateTimeZone( 'UTC' ) );
        $expect = 'Mon May 15 2023 10:30:00 GMT+0000';
        
        $this->assertEquals( $expect, $date->to_string() );
        $this->assertEquals( $expect, (string) $date );
    }

    /**
     * Test create_from_format robustness.
     */
    public function test_create_from_format() {
        $date = new Date();
        
        // Valid format
        $result = $date->create_from_format( 'Y-m-d', '2023-10-25' );
        $this->assertInstanceOf( Date::class, $result );
        $this->assertEquals( '2023-10-25', $date->format( 'Y-m-d' ) );
        
        // Invalid format
        $result = $date->create_from_format( 'Y-m-d', 'invalid-date' );
        $this->assertNull( $result );
    }

    /**
     * Test boolean check methods.
     */
    public function test_boolean_checks() {
        $past   = new Date( 'yesterday' );
        $future = new Date( 'tomorrow' );
        $today  = new Date( 'today' );
        
        $this->assertTrue( $past->is_past() );
        $this->assertFalse( $past->is_future() );
        
        $this->assertTrue( $future->is_future() );
        $this->assertFalse( $future->is_past() );
        
        $this->assertTrue( $today->is_today() );
        $this->assertTrue( $past->is_yesterday() );
        $this->assertTrue( $future->is_tomorrow() );
        
        $weekend = new Date( '2023-10-21' ); // Saturday
        $weekday = new Date( '2023-10-25' ); // Wednesday
        
        $this->assertTrue( $weekend->is_weekend() );
        $this->assertFalse( $weekend->is_weekday() );
        $this->assertTrue( $weekday->is_weekday() );
        $this->assertFalse( $weekday->is_weekend() );
    }

    /**
     * Test comparison methods.
     */
    public function test_comparison_methods() {
        $date1 = new Date( '2023-10-25 10:00:00' );
        $date2 = new Date( '2023-10-25 15:00:00' );
        $date3 = new Date( '2023-11-25 10:00:00' );
        
        $this->assertTrue( $date1->is_same_day( $date2 ) );
        $this->assertFalse( $date1->is_same_day( $date3 ) );
        
        $this->assertTrue( $date1->is_same_month( $date2 ) );
        $this->assertTrue( $date1->is_same_month( '2023-10-01' ) );
        $this->assertFalse( $date1->is_same_month( $date3 ) );
        
        $this->assertTrue( $date1->is_same_year( $date3 ) );
        $this->assertFalse( $date1->is_same_year( '2024-10-25' ) );
    }

    /**
     * Test boundary methods.
     */
    public function test_boundary_methods() {
        $date = new Date( '2023-10-25 10:30:45' );
        
        $date->start_of_day();
        $this->assertEquals( '2023-10-25 00:00:00', $date->format( 'Y-m-d H:i:s' ) );
        
        $date->end_of_day();
        $this->assertEquals( '2023-10-25 23:59:59', $date->format( 'Y-m-d H:i:s' ) );
    }

    /**
     * Test formatters.
     */
    public function test_formatters() {
        $date = new Date( '2023-10-25 10:30:45', new DateTimeZone( 'UTC' ) );
        
        $this->assertEquals( '2023-10-25T10:30:45+00:00', $date->to_iso8601() );
        $this->assertEquals( '2023-10-25', $date->to_date_string() );
        $this->assertEquals( '10:30:45', $date->to_time_string() );
    }
}
