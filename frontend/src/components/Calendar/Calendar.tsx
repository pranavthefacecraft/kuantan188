import React, { useState } from 'react';
import { 
  format, 
  startOfMonth, 
  endOfMonth, 
  startOfWeek, 
  endOfWeek, 
  addDays, 
  isSameMonth, 
  isSameDay, 
  addMonths, 
  subMonths,
  isToday,
  isBefore,
  startOfDay
} from 'date-fns';
import { Button } from 'react-bootstrap';

interface CalendarProps {
  selectedDate?: Date;
  onDateSelect: (date: Date) => void;
  minDate?: Date;
  maxDate?: Date;
  disabledDates?: Date[];
}

const Calendar: React.FC<CalendarProps> = ({ 
  selectedDate, 
  onDateSelect, 
  minDate = new Date(),
  maxDate,
  disabledDates = []
}) => {
  const [currentMonth, setCurrentMonth] = useState(selectedDate || new Date());

  const nextMonth = () => {
    setCurrentMonth(addMonths(currentMonth, 1));
  };

  const prevMonth = () => {
    setCurrentMonth(subMonths(currentMonth, 1));
  };

  const renderHeader = () => {
    return (
      <div className="d-flex justify-content-between align-items-center mb-3">
        <Button
          variant="outline-secondary"
          size="sm"
          onClick={prevMonth}
          className="calendar-nav-btn rounded-circle"
        >
          ‹
        </Button>
        <h6 className="mb-0 fw-semibold">
          {format(currentMonth, 'MMMM yyyy')}
        </h6>
        <Button
          variant="outline-secondary"
          size="sm"
          onClick={nextMonth}
          className="calendar-nav-btn rounded-circle"
        >
          ›
        </Button>
      </div>
    );
  };

  const renderDaysOfWeek = () => {
    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    return (
      <div className="row mb-2">
        {days.map(day => (
          <div key={day} className="col text-center">
            <small className="text-muted fw-medium">{day}</small>
          </div>
        ))}
      </div>
    );
  };

  const renderCells = () => {
    const monthStart = startOfMonth(currentMonth);
    const monthEnd = endOfMonth(monthStart);
    const startDate = startOfWeek(monthStart);
    const endDate = endOfWeek(monthEnd);

    const rows = [];
    let days = [];
    let day = startDate;
    let formattedDate = '';
    let rowIndex = 0;

    while (day <= endDate) {
      for (let i = 0; i < 7; i++) {
        formattedDate = format(day, 'd');
        const cloneDay = day;
        const isCurrentMonth = isSameMonth(day, monthStart);
        const isSelected = selectedDate && isSameDay(day, selectedDate);
        const isPast = isBefore(day, startOfDay(minDate));
        const isTodayDate = isToday(day);
        // eslint-disable-next-line no-loop-func
        const isDisabled = disabledDates.some(disabledDate => isSameDay(day, disabledDate)) || 
                          isPast || 
                          (maxDate && day > maxDate);

        // eslint-disable-next-line no-loop-func
        days.push(
          <div key={cloneDay.getTime()} className="col p-1">
            <button
              className={`calendar-date-btn btn w-100 rounded-circle d-flex align-items-center justify-content-center ${
                !isCurrentMonth 
                  ? 'text-muted not-current-month' 
                  : isSelected 
                    ? 'btn-success text-white' 
                    : isTodayDate
                      ? 'btn-outline-success'
                      : isDisabled
                        ? 'btn-light text-muted disabled'
                        : 'btn-outline-secondary'
              }`}
              onClick={(e) => {
                e.preventDefault();
                if (!isDisabled && isCurrentMonth) {
                  onDateSelect(cloneDay);
                }
              }}
              disabled={isDisabled || !isCurrentMonth}
            >
              {formattedDate}
            </button>
          </div>
        );
        day = addDays(day, 1);
      }
      rows.push(
        <div className="row" key={`row-${rowIndex}`}>
          {days}
        </div>
      );
      days = [];
      rowIndex++;
    }

    return <div>{rows}</div>;
  };

  return (
    <div className="calendar-component p-3 border rounded-3">
      {renderHeader()}
      {renderDaysOfWeek()}
      {renderCells()}
      <div className="mt-3">
        <small className="text-muted">
          <i className="fas fa-info-circle me-1"></i>
          Select an available date for your reservation
        </small>
      </div>
    </div>
  );
};

export default Calendar;