<template>
  <div class="schedule-calendar">
    <div class="calendar-controls">
      <button @click="prevMonth"><</button>
      <h2>{{ currentMonthName }} {{ currentYear }}</h2>
      <button @click="nextMonth">></button>
    </div>

    <div class="calendar-grid">
      <div class="calendar-header" v-for="day in dayNames" :key="day">
        {{ day }}
      </div>

      <div 
        v-for="day in calendarDays" 
        :key="day.date"
        class="calendar-day"
        :class="{
          'current-month': day.isCurrentMonth,
          'today': day.isToday,
          'has-events': day.hasEvents
        }"
        @click="selectDay(day)"
      >
        <div class="day-number">{{ day.dayNumber }}</div>
        
        <div v-if="day.events.length" class="day-events">
          <div 
            v-for="event in day.events" 
            :key="event.id"
            class="event"
            :style="{ backgroundColor: getEventColor(event) }"
            @click.stop="selectEvent(event)"
          >
            {{ event.title }}
          </div>
        </div>
      </div>
    </div>

    <div v-if="selectedEvent" class="event-details">
      <h3>{{ selectedEvent.title }}</h3>
      <p>{{ selectedEvent.description }}</p>
      <div class="event-times">
        <span>Start: {{ formatTime(selectedEvent.startDate) }}</span>
        <span>End: {{ formatTime(selectedEvent.endDate) }}</span>
      </div>
      <button @click="deselectEvent">Close</button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    schedules: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      currentDate: new Date(),
      selectedEvent: null,
      dayNames: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
    }
  },
  computed: {
    currentMonth() {
      return this.currentDate.getMonth()
    },
    currentYear() {
      return this.currentDate.getFullYear()
    },
    currentMonthName() {
      return this.currentDate.toLocaleString('default', { month: 'long' })
    },
    daysInMonth() {
      return new Date(this.currentYear, this.currentMonth + 1, 0).getDate()
    },
    firstDayOfMonth() {
      return new Date(this.currentYear, this.currentMonth, 1).getDay()
    },
    calendarDays() {
      const days = []
      const today = new Date()
      today.setHours(0, 0, 0, 0)

      // Previous month days
      const prevMonthDays = this.firstDayOfMonth
      const prevMonth = new Date(this.currentYear, this.currentMonth - 1, 1)
      const daysInPrevMonth = new Date(
        prevMonth.getFullYear(), 
        prevMonth.getMonth() + 1, 
        0
      ).getDate()

      for (let i = 0; i < prevMonthDays; i++) {
        const day = daysInPrevMonth - prevMonthDays + i + 1
        const date = new Date(this.currentYear, this.currentMonth - 1, day)
        days.push(this.createDayObject(date, false))
      }

      // Current month days
      for (let i = 1; i <= this.daysInMonth; i++) {
        const date = new Date(this.currentYear, this.currentMonth, i)
        const isToday = date.getTime() === today.getTime()
        days.push(this.createDayObject(date, true, isToday))
      }

      // Next month days
      const daysToAdd = 42 - days.length // 6 weeks
      for (let i = 1; i <= daysToAdd; i++) {
        const date = new Date(this.currentYear, this.currentMonth + 1, i)
        days.push(this.createDayObject(date, false))
      }

      return days
    }
  },
  methods: {
    createDayObject(date, isCurrentMonth, isToday = false) {
      const dayNumber = date.getDate()
      const events = this.getEventsForDate(date)
      
      return {
        date: date.toISOString().split('T')[0],
        dayNumber,
        isCurrentMonth,
        isToday,
        hasEvents: events.length > 0,
        events
      }
    },
    getEventsForDate(date) {
      const dateStr = date.toISOString().split('T')[0]
      return this.schedules.filter(schedule => {
        const startDate = new Date(schedule.startDate).toISOString().split('T')[0]
        const endDate = new Date(schedule.endDate).toISOString().split('T')[0]
        return dateStr >= startDate && dateStr <= endDate
      })
    },
    prevMonth() {
      this.currentDate = new Date(this.currentYear, this.currentMonth - 1, 1)
    },
    nextMonth() {
      this.currentDate = new Date(this.currentYear, this.currentMonth + 1, 1)
    },
    selectDay(day) {
      if (day.events.length === 1) {
        this.selectedEvent = day.events[0]
      }
    },
    selectEvent(event) {
      this.selectedEvent = event
    },
    deselectEvent() {
      this.selectedEvent = null
    },
    formatTime(dateString) {
      const date = new Date(dateString)
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    },
    getEventColor(event) {
      // Simple hash to generate consistent colors for events
      let hash = 0
      for (let i = 0; i < event.id.length; i++) {
        hash = event.id.charCodeAt(i) + ((hash << 5) - hash)
      }
      
      const hue = Math.abs(hash % 360)
      return `hsl(${hue}, 70%, 80%)`
    }
  }
}
</script>

<style scoped>
.schedule-calendar {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
}

.calendar-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.calendar-controls button {
  background: none;
  border: none;
  font-size: 1.5em;
  cursor: pointer;
  padding: 5px 15px;
}

.calendar-controls button:hover {
  background: #f5f5f5;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 5px;
}

.calendar-header {
  text-align: center;
  font-weight: bold;
  padding: 10px;
  background: #f5f5f5;
}

.calendar-day {
  min-height: 100px;
  padding: 5px;
  border: 1px solid #eee;
  background: #fff;
}

.calendar-day.current-month {
  background: #fff;
}

.calendar-day:not(.current-month) {
  background: #f9f9f9;
  color: #999;
}

.calendar-day.today {
  background: #e3f2fd;
}

.calendar-day.has-events {
  cursor: pointer;
}

.calendar-day.has-events:hover {
  background: #f5f5f5;
}

.day-number {
  text-align: right;
  font-weight: bold;
  margin-bottom: 5px;
}

.day-events {
  overflow: hidden;
}

.event {
  font-size: 0.8em;
  padding: 2px 5px;
  margin-bottom: 2px;
  border-radius: 3px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: pointer;
}

.event:hover {
  opacity: 0.8;
}

.event-details {
  margin-top: 20px;
  padding: 15px;
  background: #fff;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.event-details h3 {
  margin-top: 0;
}

.event-times {
  display: flex;
  gap: 15px;
  margin: 10px 0;
}

.event-details button {
  padding: 8px 16px;
  background: #f5f5f5;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.event-details button:hover {
  background: #e0e0e0;
}
</style>