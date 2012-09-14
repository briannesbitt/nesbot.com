<?php

class Dated
{
   public static $SECONDS_IN_DAY = 86400;
   public static $DAYS_PER_YEAR = 365.25;

   private $_timestamp;
   
   public function __get($name)
   {
      if ($name == 'year') return $this->format('Y');
      if ($name == 'month') return (int)$this->format('n');
      if ($name == 'month_text') return $this->format('M');
      if ($name == 'day') return $this->format('j');
      if ($name == 'hour') return $this->format('H');
      if ($name == 'minute') return $this->format('i');
      if ($name == 'second') return $this->format('s');
      if ($name == 'day_of_week') return $this->format('w');
      if ($name == 'timestamp') return $this->_timestamp;
   }
   public function __set($name, $value)
   {
      if ($name == 'year') $this->_timestamp = mktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $value);
      if ($name == 'month') $this->_timestamp = mktime($this->hour, $this->minute, $this->second, $value, $this->day, $this->year);
      if ($name == 'day') $this->_timestamp = mktime($this->hour, $this->minute, $this->second, $this->month, $value, $this->year);
      if ($name == 'hour') $this->_timestamp = mktime($value, $this->minute, $this->second, $this->month, $this->day, $this->year);
      if ($name == 'minute') $this->_timestamp = mktime($this->hour, $value, $this->second, $this->month, $this->day, $this->year);
      if ($name == 'second') $this->_timestamp = mktime($this->hour, $this->minute, $value, $this->month, $this->day, $this->year);
   }
   public function __toString()
   {
      return $this->toDateTimeString();
   }
      
   private function __construct($timestamp)
   {
      $this->_timestamp = $timestamp;
   }
   
   public static function now()
   {
      return new Dated(time());
   }
   public static function create($year = null, $month = null, $day = null, $hour = null, $minute = null, $second = null)
   {
      $year = ($year === null) ? date('Y') : $year;
      $month = ($month === null) ? date('n') : $month;
      $day = ($day === null) ? date('j') : $day;
      $hour = ($hour === null) ? date('G') : $hour;
      $minute = ($minute === null) ? date('i') : $minute;
      $second = ($second === null) ? date('s') : $second;

      $timestamp = mktime($hour, $minute, $second, $month, $day, $year);
      if ($timestamp === false || $timestamp == -1)
      {
         throw new exception("Invalid Dated creation");
      }
      return new Dated($timestamp);
   }
   public static function create_date($year = null, $month = null, $day = null)
   {
      return self::create($year, $month, $day, 0, 0, 0);
   }
   public static function create_time($hour = null, $minute = null, $second = null)
   {
      return self::create(null, null, null, $hour, $minute, $second);
   }
   public static function create_from_string($str)
   {
      $timestamp = strtotime($str);
      if ($timestamp === false)
      {
         throw new exception(sprintf("Invalid Dated creation from string: %s",$str));
      }
      return new Dated($timestamp);
   }
   public static function create_from_timestamp($timestamp)
   {
      if ($timestamp < 0)
      {
         throw new exception("Invalid Dated creation");
      }
      return new Dated($timestamp);
   }
   public function copy()
   {
      return new Dated($this->timestamp);
   }
   
   public function toFormattedDateString()
   {
      return $this->format('M j, Y');
   }
   public function toDateString()
   {
      return $this->format('Y-m-d');
   }
   public function toTimeString()
   {
      return $this->format('H:i:s');
   }
   public function toDateTimeString()
   {
      return $this->format('Y-m-d H:i:s');
   }
   public function toDayDateTimeString()
   {
      return $this->format('D, M j, Y h:i A');
   }
   public function toRFC822()
   {
      return $this->format('D, d M y H:i:s O');
   }
   public function format($format)
   {
      return date($format, $this->_timestamp);
   }
   public function to_human_string()
   {
      if ($this->is_future())
      {
         if (self::now()->toDateString() == $this->toDateString())
         {
            return 'Today';
         }

         if (self::now()->add_days(1)->toDateString() == $this->toDateString())
         {
            return 'Tomorrow';
         }
         
         return 'in '.floor($days_diff = ($this->timestamp - self::now()->timestamp)/(24*60*60)).' days';
      }
      
      $diff = self::now()->timestamp - $this->timestamp;
      
      if ($diff <= (60*2))
      {
         return '1 minute ago';
      }
      
      $minutes = floor($diff / 60);
      
      if ($minutes < 60)
      {
         return sprintf('%s minutes ago', $minutes);
      }
      
      $hours = floor($minutes / 60);
      
      if ($hours <= 1)
      {
         return '1 hour ago';
      }

      if ($hours < 24)
      {
         return sprintf('%d hours ago', $hours);
      }
      
      $days = floor($hours / 24);
      
      if($days <= 1)
      {
         return '1 day ago';
      }

      if($days <= 30)
      {
         return sprintf('%d days ago', $days);
      }
      
      return $this->format('M j, Y');
   }
   
   public function add_months($months)
   {
      $day = $this->day;

      $this->month += $months;
      if($day != 1 && $this->day == 1)
      {
         $this->add_days(-1);
      }
      return $this;
   }
   public function add_days($days)
   {
      $this->_timestamp += ($days * self::$SECONDS_IN_DAY);
      return $this;
   }
   public function add_hours($hours)
   {
      $this->_timestamp += ($hours * 60 * 60);
      return $this;
   }
   public function add_minutes($minutes)
   {
      $this->_timestamp += ($minutes * 60);
      return $this;
   }
   public function add_seconds($seconds)
   {
      $this->_timestamp += $seconds;
      return $this;
   }
   public function add_business_days($days)
   {
      $absolute_days = abs($days);
      $direction = $days < 0 ? -1 : 1;
      
      while ($absolute_days > 0)
      {
         $this->add_days($direction);
         
         while($this->is_weekend()) 
         { 
            $this->add_days($direction);
         }
         
         $absolute_days--;
      }
      
      return $this;
   }
   
   public function start_of_day()
   {
      $this->hour = 0;
      $this->minute = 0;
      $this->second = 0;
      return $this;
   }
   public function end_of_day()
   {
      $this->hour = 23;
      $this->minute = 59;
      $this->second = 59;
      return $this;
   }
   public function start_of_business()
   {
      $this->hour = 9;
      $this->minute = 0;
      $this->second = 0;
      return $this;
   }
   public function end_of_business()
   {
      $this->hour = 17;
      $this->minute = 30;
      $this->second = 0;
      return $this;
   }
   
   public function is_weekday()
   {
      return ($this->day_of_week != day_of_week::sunday && $this->day_of_week != day_of_week::saturday);
   }
   public function is_weekend()
   {
      return ($this->day_of_week == day_of_week::sunday || $this->day_of_week == day_of_week::saturday);
   }
   public function is_future()
   {
      return (self::now()->timestamp < $this->timestamp); 
   }
   public function is_future_day()
   {
      return (self::create_time(23,59,59)->timestamp < $this->timestamp);
   }
   public function diff_in_years(Dated $date)
   {
      return abs($this->format("md") < $date->format('md') ? $this->year - $date->year - 1 : $this->year - $date->year);
   }
   public function diff_in_days(Dated $date)
   {
      return abs(($this->timestamp - $date->timestamp)/self::$SECONDS_IN_DAY);
   }

   public function days_in_month()
   {
      return date('t', $this->timestamp); 
   }
}

class day_of_week
{
   const sunday = 0;
   const monday = 1;
   const tuesday = 2;
   const wednesday = 3;
   const thursday = 4;
   const friday = 5;
   const saturday = 6;
}