<?php

namespace WpMVC\Helpers;

defined( 'ABSPATH' ) || exit;

use DateTime;
use DateTimeZone;
use DateInterval;
use Exception;

/**
 * Class Date
 *
 * A helper class extending PHP's native DateTime to provide WordPress-aware
 * defaults and common utility methods for date and time manipulation.
 *
 * @package WpMVC\Helpers
 */
class Date extends DateTime {
    /**
     * Seconds per minute.
     */
    const SECONDS_PER_MINUTE = 60;

    /**
     * Minutes per hour.
     */
    const MINUTES_PER_HOUR = 60;

    /**
     * Hours per day.
     */
    const HOURS_PER_DAY = 24;

    /**
     * Days per week.
     */
    const DAYS_PER_WEEK = 7;

    /**
     * Months per quarter.
     */
    const MONTHS_PER_QUARTER = 3;

    /**
     * Quarters per year.
     */
    const QUARTERS_PER_YEAR = 4;

    /**
     * Date constructor.
     *
     * @param string            $datetime Optional. The date/time string. Defaults to "now".
     * @param DateTimeZone|null $timezone Optional. The timezone. Defaults to wp_timezone().
     */
    public function __construct( string $datetime = 'now', ?DateTimeZone $timezone = null ) {
        parent::__construct( $datetime, $timezone ?? wp_timezone() );
    }

    /**
     * Create a new instance representing the current time.
     * @param DateTimeZone|null $timezone Optional. The timezone. Defaults to wp_timezone().
     * @return static
     */
    public static function now( ?DateTimeZone $timezone = null ) {
        return new static( 'now', $timezone );
    }

    /**
     * Perform addition or subtraction on a specific date unit.
     *
     * @param string $unit  The unit (minute, hour, day, week, month, quarter, year).
     * @param int    $value The number of units.
     * @param string $type  The type of operation ('add' or 'sub').
     *
     * @return $this
     * @throws Exception If the unit is invalid.
     */
    public function unit( string $unit, int $value = 1, string $type = 'add' ) {
        $interval_spec = '';

        switch ( $unit ) {
            case 'minute':
                $interval_spec = "PT{$value}M";
                break;
            case 'hour':
                $interval_spec = "PT{$value}H";
                break;
            case 'day':
                $interval_spec = "P{$value}D";
                break;
            case 'week':
                $interval_spec = "P{$value}W";
                break;
            case 'month':
                $interval_spec = "P{$value}M";
                break;
            case 'quarter':
                $months        = $value * static::MONTHS_PER_QUARTER;
                $interval_spec = "P{$months}M";
                break;
            case 'year':
                $interval_spec = "P{$value}Y";
                break;
            default:
                // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
                throw new Exception( "Invalid unit for date addition/subtraction: '$unit'" );
        }

        $interval = new DateInterval( $interval_spec );

        if ( 'sub' === $type ) {
            $this->sub( $interval );
        } else {
            $this->add( $interval );
        }

        return $this;
    }

    /**
     * Add a specific number of days.
     *
     * @param int $days The number of days to add.
     *
     * @return $this
     */
    public function add_days( int $days ) {
        return $this->unit( 'day', $days );
    }

    /**
     * Subtract a specific number of days.
     *
     * @param int $days The number of days to subtract.
     *
     * @return $this
     */
    public function sub_days( int $days ) {
        return $this->unit( 'day', $days, 'sub' );
    }

    /**
     * Add a specific number of months.
     *
     * @param int $months The number of months to add.
     *
     * @return $this
     */
    public function add_months( int $months ) {
        return $this->unit( 'month', $months );
    }

    /**
     * Subtract a specific number of months.
     *
     * @param int $months The number of months to subtract.
     *
     * @return $this
     */
    public function sub_months( int $months ) {
        return $this->unit( 'month', $months, 'sub' );
    }

    /**
     * Add a specific number of years.
     *
     * @param int $years The number of years to add.
     *
     * @return $this
     */
    public function add_years( int $years ) {
        return $this->unit( 'year', $years );
    }

    /**
     * Subtract a specific number of years.
     *
     * @param int $years The number of years to subtract.
     *
     * @return $this
     */
    public function sub_years( int $years ) {
        return $this->unit( 'year', $years, 'sub' );
    }

    /**
     * Convert to a standard string format.
     *
     * @return string
     */
    public function to_string() {
        return $this->format( 'D M d Y H:i:s \G\M\TO' );
    }

    /**
     * Magic method for string representation.
     *
     * @return string
     */
    public function __toString() {
        return $this->to_string();
    }

    /**
     * Create a new instance from a specific format.
     *
     * @param string            $format   The format string.
     * @param string            $datetime The date/time string.
     * @param DateTimeZone|null $timezone Optional. The timezone.
     *
     * @return $this|null Returns null if creation fails.
     */
    public function create_from_format( string $format, string $datetime, ?DateTimeZone $timezone = null ) {
        $instance = static::createFromFormat( $format, $datetime, $timezone );

        if ( ! $instance ) {
            return null;
        }

        $this->setTimestamp( $instance->getTimestamp() );

        return $this;
    }

    /**
     * Add a specific number of seconds.
     *
     * @param int $time The number of seconds to add.
     *
     * @return $this
     */
    public function add_timestamp( int $time ) {
        $this->setTimestamp( $this->getTimestamp() + $time );
        return $this;
    }

    /**
     * Check if the date is in the past.
     *
     * @return bool
     */
    public function is_past() {
        return $this->getTimestamp() < time();
    }

    /**
     * Check if the date is in the future.
     *
     * @return bool
     */
    public function is_future() {
        return $this->getTimestamp() > time();
    }

    /**
     * Check if the date is today.
     *
     * @return bool
     */
    public function is_today() {
        $now = new static( 'now', $this->getTimezone() );
        return $this->format( 'Y-m-d' ) === $now->format( 'Y-m-d' );
    }

    /**
     * Check if the date is yesterday.
     *
     * @return bool
     */
    public function is_yesterday() {
        $yesterday = new static( 'yesterday', $this->getTimezone() );
        return $this->format( 'Y-m-d' ) === $yesterday->format( 'Y-m-d' );
    }

    /**
     * Check if the date is tomorrow.
     *
     * @return bool
     */
    public function is_tomorrow() {
        $tomorrow = new static( 'tomorrow', $this->getTimezone() );
        return $this->format( 'Y-m-d' ) === $tomorrow->format( 'Y-m-d' );
    }

    /**
     * Check if the date falls on a weekend.
     *
     * @return bool
     */
    public function is_weekend() {
        $day = $this->format( 'N' );
        return $day >= 6;
    }

    /**
     * Check if the date falls on a weekday.
     *
     * @return bool
     */
    public function is_weekday() {
        return ! $this->is_weekend();
    }

    /**
     * Check if the instance represents the same day as another.
     *
     * @param mixed $other String or DateTime object.
     *
     * @return bool
     */
    public function is_same_day( $other ) {
        $other = $other instanceof DateTime ? $other : new static( $other );
        return $this->format( 'Y-m-d' ) === $other->format( 'Y-m-d' );
    }

    /**
     * Check if the instance represents the same month as another.
     *
     * @param mixed $other String or DateTime object.
     *
     * @return bool
     */
    public function is_same_month( $other ) {
        $other = $other instanceof DateTime ? $other : new static( $other );
        return $this->format( 'Y-m' ) === $other->format( 'Y-m' );
    }

    /**
     * Check if the instance represents the same year as another.
     *
     * @param mixed $other String or DateTime object.
     *
     * @return bool
     */
    public function is_same_year( $other ) {
        $other = $other instanceof DateTime ? $other : new static( $other );
        return $this->format( 'Y' ) === $other->format( 'Y' );
    }

    /**
     * Set the time to the beginning of the day (00:00:00).
     *
     * @return $this
     */
    public function start_of_day() {
        $this->setTime( 0, 0, 0 );
        return $this;
    }

    /**
     * Set the time to the end of the day (23:59:59).
     *
     * @return $this
     */
    public function end_of_day() {
        $this->setTime( 23, 59, 59 );
        return $this;
    }

    /**
     * Format as ISO 8601 string.
     *
     * @return string
     */
    public function to_iso8601() {
        return $this->format( DateTime::ATOM );
    }

    /**
     * Format as a date-only string (Y-m-d).
     *
     * @return string
     */
    public function to_date_string() {
        return $this->format( 'Y-m-d' );
    }

    /**
     * Format as a time-only string (H:i:s).
     *
     * @return string
     */
    public function to_time_string() {
        return $this->format( 'H:i:s' );
    }
}
