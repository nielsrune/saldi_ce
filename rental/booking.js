// Make sure you're in an async context
(async () => {
  const url = new URL(window.location.href)
  const pathSegments = url.pathname.split('/').filter(segment => segment !== '')
  const firstFolder = pathSegments[0]
  // Dynamically import the module
  const { getAllCustomers, getCustomers, getReservationsByItem, deleteReservationByItem, getSettings, createReservation, createBooking, createOrder, getClosedDays, getItem, getItemBookings } = await import(`/${firstFolder}/rental/api/api.js`)

const cust = document.querySelector(".customer")

const settings = await getSettings()

const createOptionElement = (value, text) => {
  const option = document.createElement("option")
  option.value = value
  option.text = text
  return option
}

const createOptionElementDatalist = (value, text) => {
  const option = document.createElement("option")
  option.dataset.value = value
  option.text = text
  return option
}

const formatDate = (date) => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

// Function to find closed dates within a given range
const findClosedDates = (closedDays, fromDate, toDate) => {
  const closedDates = []

  closedDays.forEach(closedDay => {
    if (closedDay >= fromDate && closedDay <= toDate) {
      closedDates.push(closedDay)
    }
  })

  return closedDates
}

// Function to adjust the booking end date to avoid closed dates
/* const adjustBookingEndDateIfNeeded = async (fromDate, toDate) => {
  const closedDays = await getClosedDays()
  if(closedDays === "Der er ingen lukkede dage"){
    return toDate
  }
  const fromDateData = new Date(fromDate)
  const toDateData = new Date(toDate)

  const closedDatesInRange = findClosedDates(closedDays, fromDateData, toDateData)

  if (closedDatesInRange.length > 0) {
    closedDatesInRange.forEach(closedDate => {
      toDateData.setDate(toDateData.getDate() + 1)
    })

    return formatDate(toDateData)
  } else {
    return toDate // Return the original toDate if there are no closed dates in the range
  }
} */

const getCustomerDates = async () => {
  const data = await getCustomers()
  if(data === "Der er ingen bookinger"){
      return []
  }
  return data.map(i => {
      const fromDate = new Date(i.from * 1000)
      const fromDateFormattad = fromDate.getFullYear() + "-" + ("0" + (fromDate.getMonth() + 1)).slice(-2) + "-" + ("0" + fromDate.getDate()).slice(-2)
      const toDate = new Date(i.to * 1000)
      const toDateFormattad = toDate.getFullYear() + "-" + ("0" + (toDate.getMonth() + 1)).slice(-2) + "-" + ("0" + toDate.getDate()).slice(-2)
      const id = i.item_id

      return [fromDateFormattad, toDateFormattad, id]
  })
}

const getCustomerTimes = async () => {
  const data = await getCustomers()
  if(data == "Der er ingen bookinger"){
      return []
  }
  return data.map(i => {
      const fromTime = new Date(i.from * 1000)
      const fromTimeFormatted = fromTime.getHours() + ":" + fromTime.getMinutes()
      const toTime = new Date(i.to * 1000)
      const toTimeFormatted = toTime.getHours() + ":" + toTime.getMinutes()
      return [fromTimeFormatted, toTimeFormatted]
  })
}

  const createInputElement = (type, name, className) => {
    const input = document.createElement("input")
    input.setAttribute("type", type)
    input.setAttribute("name", name)
    input.setAttribute("class", className)
    return input
  }

  const createLabelAndDivElement = (className, labelText, element) => {
    const div = document.createElement("div")
    const label = document.createElement("label")
    label.textContent = labelText
    div.setAttribute("class", className)
    div.appendChild(label)
    div.appendChild(element)
    return div
  }

  const appendToFormGroup = (rootElement, elements) => {
    elements.forEach(element => {
      rootElement.appendChild(element)
    })
  }

  const addEventListeners = (elements, event, handler) => {
    elements.forEach(element => {
      element.addEventListener(event, handler)
    })
  }

/* const drawForm = async () => {
  const itemsSelect = document.querySelector(".items")
  while(itemsSelect.firstChild){
    itemsSelect.removeChild(itemsSelect.firstChild)
  }
  itemsSelect.innerHTML = "<option value='' disabled selected hidden>Vælg dato/tidsrum før du vælgere vare</option>"
  const items = await getAllItems()

  if (settings.booking_format === "1") {
    const rootElement = document.querySelector(".changing-input")
    const fromDate = createInputElement("date", "fromDate", "form-control")
    const fromDateDiv = createLabelAndDivElement("form-group", "dato", fromDate)
    const fromTime = createInputElement("time", "fromTime", "form-control")
    const fromTimeDiv = createLabelAndDivElement("form-group col-6", "Fra", fromTime)
    const toTime = createInputElement("time", "toTime", "form-control")
    const toTimeDiv = createLabelAndDivElement("form-group col-6", "Til", toTime)
    appendToFormGroup(rootElement, [fromDateDiv, fromTimeDiv, toTimeDiv])

    let fromDateData, fromTimeData, toTimeData

    const handleTimeChange = () => {
      fromDateData = fromDate.value
      fromTimeData = fromTime.value
      toTimeData = toTime.value
      getItems()
    }

    addEventListeners([fromDate, fromTime, toTime], "change", handleTimeChange)

    const getItems = async () => {
      if (fromDateData && fromTimeData && toTimeData) {
        if(fromTimeData > toTimeData){
          alert("Du kan ikke velge en til tid som er før fra tid")
          return
        }
        const itemsUnavailable = []

        const dates = await getCustomerDates()
        const times = await getCustomerTimes()
        const [year, month, day] = fromDateData.split("-")
        const [fromHours, fromMinutes] = fromTimeData.split(":")
        const [toHours, toMinutes] = toTimeData.split(":")
        const resevationDate = year + "-" + month + "-" + day
        const fromTime = fromHours + ":" + fromMinutes
        const toTime = toHours + ":" + toMinutes
        dates.forEach(date => {
          if(resevationDate == date[0]){
            times.forEach(time => {
              if((fromTime >= time[0] && fromTime <= time[1]) || (toTime >= time[0] && toTime <= time[1])){
                itemsUnavailable.push(date[2])
              }
            })
          }
        })

        items.forEach(i => {
          if(!itemsUnavailable.includes(i.id)){
            const option = createOptionElement(i.id, i.item_name)
            itemsSelect.appendChild(option)
          }
        })
        document.querySelector(".form").addEventListener("submit", async e => {
          e.preventDefault()
          if(itemsSelect.value === undefined){
            alert("Du skal vælge en vare")
            return
          }
          const data = {
            customer_id: cust.value,
            item_id: itemsSelect.value,
            from: new Date(fromDateData + "T" + fromTimeData)/1000,
            to: new Date(fromDateData + "T" + toTimeData)/1000
          }
          const res = await createBooking(data)
          alert(res)
          window.location.href = "/pos/rental/index.php"
        })
      }
    }
  }else{

    // setup product select
    const productDiv = document.querySelector(".product")
    const product = document.createElement("div")
    product.setAttribute("class", "form-group")
    const productLabel = document.createElement("label")
    productLabel.textContent = "Stand type"
    const productSelect = document.createElement("select")
    productSelect.setAttribute("class", "form-control products")
    productSelect.innerHTML = "<option value='0' selected>Vælg stand type</option>"
    product.appendChild(productLabel)
    product.appendChild(productSelect)
    productDiv.appendChild(product)

    // get reservations
    const reservations = await getReservations()

    // get all product names
    const productNames = await getAllProductNames()
    productNames.forEach(p => {
      const option = createOptionElement(p.product_id, p.product_name)
      productSelect.appendChild(option)
    })

    const searchInput = document.querySelector(".customers-search")

    const optionsList = document.getElementById("customers").options
    let selectedText
    searchInput.addEventListener("change", () => {
        const selectedValue = searchInput.value
        
        for (let i = 0; i < optionsList.length; i++) {
            const option = optionsList[i]
            if (option.text === selectedValue) {
                selectedText = option.dataset.value
                break // Exit loop once we find the matching option
            }
        }
    })



    const closedDays = await getClosedDays()
    const closedDates = []
    if(closedDays !== "Der er ingen lukkede dage"){
      closedDays.forEach(i => {
        const date = new Date(i.date * 1000)
        const year = date.getFullYear()
        const month = (date.getMonth() + 1 < 10) ? "0" + (date.getMonth() + 1) : date.getMonth() + 1
        const day = (date.getDate() < 10) ? "0" + date.getDate() : date.getDate()
        const formattedDate = year + "-" + month + "-" + day
        closedDates.push(formattedDate)
      })
    }

    const fromCalendar = document.querySelector(".from")
    const toCalendar = document.querySelector(".to")
    let fromDateData, toDateData, fromDate, addedDays = 0
    const addedDaysArray = []

    flatpickr(fromCalendar, {
      dateFormat: 'Y-m-d',
      minDate: 'today',
      theme: "dark",
      locale: "da",
      disable: closedDates,
      onChange: (selectedDates, dateStr, instance) => {
        fromDateData = dateStr
        const [year, month, day] = dateStr.split('-').map(Number)
        fromDate =  new Date(year, month - 1, day)
        flatpickr(toCalendar, {
          dateFormat: 'Y-m-d',
          minDate: fromDate,
          theme: "dark",
          locale: "da",
          disable: closedDates,
          onDayCreate: function(dObj, dStr, fp, dayElem) {
            if (settings.find_weeks === "1" && fromDate != undefined && fromDate != "" && fromDate != "Invalid Date") {
              const date = new Date(dayElem.dateObj)
              const dateStr = date.getFullYear() + "-" + ("0" + (date.getMonth() + 1)).slice(-2) + "-" + ("0" + date.getDate()).slice(-2)
              if(closedDates.includes(dateStr) && !addedDaysArray.includes(dateStr)){
                addedDaysArray.push(dateStr)
              }
              const daysToAdd = addedDaysArray.filter(d => d >= fromDateData && d <= dateStr)
              addedDays = daysToAdd.length
              const timeDifference = Math.abs(date - fromDate)
              const daysDifference = Math.floor(timeDifference / (1000 * 60 * 60 * 24)) + 1 - addedDays
              if(daysDifference % 7 === 0 && daysDifference !== 0 && daysDifference !== 1 && closedDates.includes(dateStr) === false){
                dayElem.className += " has-action";
              }
            }
          },
          onChange: (selectedDates, dateStr, instance) => {
            toDateData = dateStr
            if(toDateData === undefined || toDateData === "" || fromDateData === undefined || fromDateData === "" || toDateData === "Invalid Date" || fromDateData === "Invalid Date"){
              return
            }
      getItems()
    }
        })
        if(toDateData === undefined || toDateData === "" || fromDateData === undefined || fromDateData === "" || toDateData === "Invalid Date" || fromDateData === "Invalid Date"){
          return
        }

        getItems()
      }
    })
    
    const productChange = async () => {
      getItems()
    }

    productSelect.addEventListener("change", productChange)
    let diffDays
    const getItems = async () => {
      if(fromDateData && toDateData) {
        if(fromDateData > toDateData){
          alert("Du kan ikke vælge en til dato som er før fra dato")
          return
        }
        
        itemsSelect.innerHTML = ""

        const itemsUnavailable = []

        const dates = await getCustomerDates()

        dates.forEach(date => {
          // add 1 day to the from date to avoid conflicts with the end date of the previous booking
          let newFromDate = new Date(date[0])
          //newFromDate.setDate(newFromDate.getDate() + 1)
          newFromDate = newFromDate.getFullYear() + "-" + ("0" + (newFromDate.getMonth() + 1)).slice(-2) + "-" + ("0" + newFromDate.getDate()).slice(-2)

          // remove 1 day from the to date to avoid conflicts with the start date of the next booking
          let newToDate = new Date(date[1])
          //newToDate.setDate(newToDate.getDate() - 1)
          newToDate = newToDate.getFullYear() + "-" + ("0" + (newToDate.getMonth() + 1)).slice(-2) + "-" + ("0" + newToDate.getDate()).slice(-2)

          // check if the selected dates are within the booked dates
          if((newFromDate <= toDateData && newFromDate >= fromDateData) ||
          (newToDate >= fromDateData && newToDate <= toDateData) ||
          (newFromDate <= fromDateData && newToDate >= toDateData)){
            itemsUnavailable.push(date[2])
          }
        })

        const productId = productSelect.value

        const availableItems = []
        items.forEach(i => {
          if(!itemsUnavailable.includes(i.id.toString())){
            if(i.product_id == productId || productId == 0){
              let option
              if(i.reserved == 1){
                option = createOptionElement(i.id, i.item_name + " (Spærret)")
              }else{
                option = createOptionElement(i.id, i.item_name)
              }
              availableItems.push(option)
            itemsSelect.appendChild(option)
          }
          }
        })

        // check if there is a week between previous booking and the selected date ignore closed dates
        
        if(availableItems.length === 0){
          const option = createOptionElement(0, "Der er ingen ledige stande")
          itemsSelect.appendChild(option)
        }
        const closedDays = findClosedDates(closedDates, fromDateData, toDateData)
        const info = document.querySelector(".info")
        const diffTime = Math.abs(new Date(toDateData) - new Date(fromDateData))
        diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1
        diffDays = diffDays - closedDays.length
        info.innerHTML = Math.floor(diffDays/7) + " uger" + " og " + diffDays%7 + " dage"
        //toDateData = await adjustBookingEndDateIfNeeded(fromDateData, toDateData)
      }
    }
    const form = document.querySelector(".form")
        
    form.addEventListener("submit", async e => {
          e.preventDefault()
      e.stopPropagation()

          if(itemsSelect.value === undefined){
        alert("Du skal vælge en stand")
        return
      }
      if(selectedText === undefined || selectedText === ""){
        alert("Du skal vælge en kunde")
        return
      }

      
      // check if the selected item is reserved in the selected time
      const reservedItems = []

      reservations.forEach(r => {
        const fromDate = new Date(r.from * 1000)
        const fromDateFormattad = fromDate.getFullYear() + "-" + ("0" + (fromDate.getMonth() + 1)).slice(-2) + "-" + ("0" + fromDate.getDate()).slice(-2)
        const toDate = new Date(r.to * 1000)
        const toDateFormattad = toDate.getFullYear() + "-" + ("0" + (toDate.getMonth() + 1)).slice(-2) + "-" + ("0" + toDate.getDate()).slice(-2)
        const id = r.item_id
        // check if the selected dates are within the reserved dates
        if((fromDateFormattad <= toDateData && fromDateFormattad >= fromDateData) ||
        (toDateFormattad >= fromDateData && toDateFormattad <= toDateData) ||
        (fromDateFormattad <= fromDateData && toDateFormattad >= toDateData)){
          reservedItems.push(id)
        }
      })
      
      // if selected item is in reservedItems then prompt user
      if(reservedItems.includes(itemsSelect.value)){
        const confirm = window.confirm("Den valgte stand er spærret i det valgte tidsrum. Vil du fortsætte?")
        if(!confirm){
            return
          }
      }
      
      // get number of days including the start and end dates
      const diffTime = Math.abs(new Date(toDateData) - new Date(fromDateData))
      //let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1
          const fromDate = new Date(fromDateData)/1000
          const toDate = new Date(toDateData)/1000
          const data = {
        customer_id: selectedText,
            item_id: itemsSelect.value,
            from: fromDate,
        to: toDate,
        fromDate: fromDateData,
        toDate: toDateData,
        days: diffDays
          }
      const loading = document.querySelector("#loading")
      loading.style.display = "flex"
          const res = await createBooking(data)
      data.booking_id = res.id

      await createOrder(data)
      loading.style.display = "none"
      alert(res.msg)
      window.location.href = "/pos/rental/index.php?vare"
    })
  }
} */

const singleItem = async (item) => {
  const searchInput = document.querySelector(".customers-search")

    const optionsList = document.getElementById("customers").options
    let selectedText
    searchInput.addEventListener("change", () => {
        const selectedValue = searchInput.value
        
        for (let i = 0; i < optionsList.length; i++) {
            const option = optionsList[i]
            if (option.text === selectedValue) {
                selectedText = option.dataset.value
                break // Exit loop once we find the matching option
            }
        }
    })

  // item already selected
  const itemsSelect = document.querySelector(".items")
  itemsSelect.innerHTML = ""

  const itemData = await getItem(item)
  const option = createOptionElement(itemData.id, itemData.item_name)
  itemsSelect.appendChild(option)
  
  // get bookings for selected item
  const bookings = await getItemBookings(item)
  const dates = []
  if(bookings.msg !== "Der er ingen bookinger"){
    bookings.forEach(b => {
      const fromDate = new Date(b.from * 1000)
      const fromDateFormattad = fromDate.getFullYear() + "-" + ("0" + (fromDate.getMonth() + 1)).slice(-2) + "-" + ("0" + fromDate.getDate()).slice(-2)
      const toDate = new Date(b.to * 1000)
      const toDateFormattad = toDate.getFullYear() + "-" + ("0" + (toDate.getMonth() + 1)).slice(-2) + "-" + ("0" + toDate.getDate()).slice(-2)
      dates.push([fromDateFormattad, toDateFormattad])
    })
  }

  // get disabled dates from closed days
  const closedDays = await getClosedDays()
  const closedDates = []
  if(closedDays.success !== false){
    closedDays.forEach(i => {
      const date = new Date(i.date * 1000)
      const year = date.getFullYear()
      const month = (date.getMonth() + 1 < 10) ? "0" + (date.getMonth() + 1) : date.getMonth() + 1
      const day = (date.getDate() < 10) ? "0" + date.getDate() : date.getDate()
      const formattedDate = year + "-" + month + "-" + day
      closedDates.push(formattedDate)
    })
  }
  // Helper function to generate dates between two dates
  function generateDates(startDate, endDate) {
    const dates = []
    const currentDate = new Date(startDate)
    const end = new Date(endDate)

    // Normalize the time component to midnight for both dates
    currentDate.setHours(0, 0, 0, 0)
    end.setHours(0, 0, 0, 0)
    
    while (currentDate <= endDate) {
      const year = currentDate.getFullYear()
      const month = (currentDate.getMonth() + 1 < 10) ? "0" + (currentDate.getMonth() + 1) : currentDate.getMonth() + 1
      const day = (currentDate.getDate() < 10) ? "0" + currentDate.getDate() : currentDate.getDate()
      const formattedDate = year + "-" + month + "-" + day
      dates.push(formattedDate)
      
      // Move to the next day
      currentDate.setDate(currentDate.getDate() + 1)
    }
    
    return dates
  }

  // get disabled dates from bookings 
  const allDatesWithoutEnds = []
  const allDatesWithoutStarts = []
  if(dates){
    dates.forEach(d => {
      const [fromDate, toDate] = d
      const fromDateObj = new Date(fromDate)
      const toDateObj = new Date(toDate)
      
      // Exclude the end date and add it to the allDatesWithoutEnds array
      toDateObj.setDate(toDateObj.getDate() - 1)
      const endDates = generateDates(fromDateObj, toDateObj)
      allDatesWithoutEnds.push(...endDates)
      
      // Exclude the start date and add it to the allDatesWithoutStarts array
      const startDates = generateDates(new Date(fromDate), new Date(toDate))
      startDates.shift() // Remove the first element (start date)
      allDatesWithoutStarts.push(...startDates)
    })

    // Now we have two separate arrays: allDatesWithoutEnds and allDatesWithoutStarts
  }
  if(closedDates){
    closedDates.forEach(c => {
      allDatesWithoutEnds.push(c)
      allDatesWithoutStarts.push(c)
    })
  }

  // get already booked dates
  const bookedDates = []
  if(dates){
    dates.forEach(d => {
      const [fromDate, toDate] = d
      const fromDateObj = new Date(fromDate)
      const toDateObj = new Date(toDate)
      const dates = generateDates(fromDateObj, toDateObj)
      bookedDates.push(...dates)
    })
  }
  
  // draw form
  const fromCalendar = document.querySelector(".from")
  const toCalendar = document.querySelector(".to")
  let fromDateData, toDateData, fromDate, addedDays, lastDay
  const addedDaysArray = []
  closedDates.push(...bookedDates)

  

  const datePick = flatpickr(fromCalendar, {
    dateFormat: 'Y-m-d',
    theme: "dark",
    locale: "da",
    disable: closedDates,
    onDayCreate: function(dObj, dStr, fp, dayElem) {
      // Sort bookings in descending order based on the 'to' property
      if (bookings.msg === "Der er ingen bookinger") {
        return;
      }
      bookings.sort((a, b) => b.to - a.to);
    
      // Get the current date
      const currentDate = new Date(dayElem.dateObj);
    
      // Find the latest booking date before the current date
      let latestBookingDate = null;
      for (const booking of bookings) {
        const bookingDate = new Date(booking.to * 1000);
        if (bookingDate < currentDate && (!latestBookingDate || bookingDate > latestBookingDate)) {
          latestBookingDate = bookingDate;
        }
      }
    
      // If a latest booking date was found, add 1 day to avoid conflicts
      if (latestBookingDate) {
        latestBookingDate.setDate(latestBookingDate.getDate() + 1);
      }
    
      const date = new Date(dayElem.dateObj);
      const dateStr = date.getFullYear() + "-" + ("0" + (date.getMonth() + 1)).slice(-2) + "-" + ("0" + date.getDate()).slice(-2);
    
      if (closedDates.includes(dateStr) && !addedDaysArray.includes(dateStr)) {
        addedDaysArray.push(dateStr);
      }
      const daysToAdd = addedDaysArray.filter(d => d >= fromDateData && d <= dateStr);
      addedDays = daysToAdd.length;
    
      // Calculate the difference between dayElem.dateObj and the latest booking date
      const timeDifference = Math.abs(date - latestBookingDate);
      const daysDifference = Math.round(timeDifference / (1000 * 60 * 60 * 24)) + 1 - addedDays;
      console.log(daysDifference);
    
      // Check if the difference is equal to or greater than 7 and there is no booking on dayElem.dateObj
      if (date > latestBookingDate && daysDifference % 7 === 0 && !bookings.some(booking => new Date(booking.from * 1000).toDateString() === dayElem.dateObj.toDateString()) && !closedDates.includes(dateStr)) {
        // Add class to dayElem
        dayElem.className += " has-action";
      }
    },
    onChange: (selectedDates, dateStr, instance) => {
      fromDateData = dateStr
      const [year, month, day] = dateStr.split('-').map(Number)
      fromDate =  new Date(year, month - 1, day)
      // if from date is before a date in bookedDates then disable all dates after that date
      if(bookedDates){
        bookedDates.some(d => {
          const [year, month, day] = d.split('-').map(Number)
          const bookedDate = new Date(year, month - 1, day)
          if(fromDate < bookedDate){
            lastDay = bookedDate
            return true
          }
        })
      }
      flatpickr(toCalendar, {
        dateFormat: 'Y-m-d',
        minDate: fromDate,
        maxDate: lastDay,
        theme: "dark",
        locale: "da",
        disable: closedDates,
        onDayCreate: function(dObj, dStr, fp, dayElem) {
          if (settings.find_weeks === "1" && fromDate != undefined && fromDate != "" && fromDate != "Invalid Date") {
            const date = new Date(dayElem.dateObj)
            const dateStr = date.getFullYear() + "-" + ("0" + (date.getMonth() + 1)).slice(-2) + "-" + ("0" + date.getDate()).slice(-2)
            if(closedDates.includes(dateStr) && !addedDaysArray.includes(dateStr)){
              addedDaysArray.push(dateStr)
            }
            const daysToAdd = addedDaysArray.filter(d => d >= fromDateData && d <= dateStr)
            addedDays = daysToAdd.length
            const timeDifference = Math.abs(date - fromDate)
            const daysDifference = Math.round(timeDifference / (1000 * 60 * 60 * 24)) + 1 - addedDays
            if(daysDifference % 7 === 0 && daysDifference !== 0 && daysDifference !== 1 && closedDates.includes(dateStr) === false){
              dayElem.className += " has-action"
            }
          }
        },
        onChange: (selectedDates, dateStr, instance) => {
          toDateData = dateStr
          if(toDateData === undefined || toDateData === "" || fromDateData === undefined || fromDateData === "" || toDateData === "Invalid Date" || fromDateData === "Invalid Date"){
            return
          }
          update()
        }
      })
      if(toDateData === undefined || toDateData === "" || fromDateData === undefined || fromDateData === "" || toDateData === "Invalid Date" || fromDateData === "Invalid Date"){
        return
      }

      update()
    }
  })
  
  const queryString = window.location.search
  const urlParams = new URLSearchParams(queryString)
  if(urlParams.has("time")){
    // set date to url param
    const getDate = new Date(urlParams.get("time"))
    datePick.setDate(getDate)

    // trigger flatpickr onChange event
    datePick.config.onChange[0](datePick.selectedDates, datePick.input.value, datePick)
  }
  

  const update = () => {
    let diffDays
    const closedDays = findClosedDates(closedDates, fromDateData, toDateData)
    const info = document.querySelector(".info")
    const diffTime = Math.abs(new Date(toDateData) - new Date(fromDateData))
    diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1
    diffDays = diffDays - closedDays.length
    info.innerHTML = Math.floor(diffDays/7) + " uger" + " og " + diffDays%7 + " dage"
  }

  document.querySelector(".form").addEventListener("submit", async e => {
    e.preventDefault() // Prevent the form submission
    e.stopPropagation()
    // prevent further submission if the form is already submitted
    if (e.target.classList.contains("submitted")) {
      return
    }
    e.target.classList.add("submitted") // Add a class to the form to prevent further submission
    if (fromDateData > toDateData) {
      alert("Du kan ikke vælge en til dato som er før fra dato")
      return
    }
    if (fromDateData === undefined || toDateData === undefined || fromDateData === "" || toDateData === "") {
      alert("Du skal vælge en dato")
      return
    }
    if (selectedText === undefined || selectedText === "") {
      alert("Du skal vælge en kunde")
      return
    }

    // check if the selected item is reserved in or after the selected time
    let reservedItems = false

    const reservations = await getReservationsByItem(item)
    if(reservations.success !== false){
      reservations.forEach(r => {
        if(r.item_id == item){
          const fromDate = new Date(r.from * 1000)
          const fromDateFormattad = fromDate.getFullYear() + "-" + ("0" + (fromDate.getMonth() + 1)).slice(-2) + "-" + ("0" + fromDate.getDate()).slice(-2)
          const toDate = new Date(r.to * 1000)
          const toDateFormattad = toDate.getFullYear() + "-" + ("0" + (toDate.getMonth() + 1)).slice(-2) + "-" + ("0" + toDate.getDate()).slice(-2)
          // check if the selected dates are within the reserved dates
          if((fromDateFormattad <= toDateData && fromDateFormattad >= fromDateData) ||
            (toDateFormattad >= fromDateData && toDateFormattad <= toDateData) ||
            (fromDateFormattad <= fromDateData && toDateFormattad >= toDateData) ||
            (fromDateFormattad < fromDateData && toDateFormattad > toDateData)){
              reservedItems = true
          }
        }
      })
    }

    // if selected item is in reservedItems then prompt user
    if(reservedItems){
      const confirm = window.confirm("Den valgte stand er spærret i det valgte tidsrum. Vil du fortsætte?")
      if(!confirm){
        return
      }
    }

    // make sure there is no booking in the range of the selected dates 
    if (dates.length > 0) {
      let hasBookingConflict = false // Flag to track conflicts
      for (const [fromDate, toDate] of dates) {
        // add 1 day to the from date to avoid conflicts with the end date of the previous booking
        let newFromDate = new Date(fromDate)
        newFromDate = newFromDate.getFullYear() + "-" + ("0" + (newFromDate.getMonth() + 1)).slice(-2) + "-" + ("0" + newFromDate.getDate()).slice(-2)

        // remove 1 day from the to date to avoid conflicts with the start date of the next booking
        let newToDate = new Date(toDate)
        newToDate = newToDate.getFullYear() + "-" + ("0" + (newToDate.getMonth() + 1)).slice(-2) + "-" + ("0" + newToDate.getDate()).slice(-2)

        if (
          (fromDateData <= newToDate && fromDateData >= newFromDate) ||
          (toDateData >= newFromDate && toDateData <= newToDate) ||
          (fromDateData <= newFromDate && toDateData >= newToDate)
        ) {
          alert("Der er allerede en booking i den valgte periode")
          hasBookingConflict = true // Set the flag to true
          break // Exit the loop when there's a conflict
        }
      }
  
      // If there's a booking conflict, prevent the form submission
      if (hasBookingConflict) {
        return
      }
    }


    const reservation = document.querySelector(".reservation")
    // check if reservation is checked
    if(reservation.checked){
      const res = await getReservationsByItem(item)
      if(res.success != false){
        if(confirm("Der er allerede en spærring på denne stand, vil du fjerne den og lave en ny?") !== false){
          await deleteReservationByItem(item)
          await createReservation({
            item_id: item,
            from: (new Date(toDateData).getTime() + 24 * 60 * 60 * 1000) / 1000, // a day after the last day in the range
            to: (new Date(toDateData).getTime() + 24 * 60 * 60 * 1000 * (365 * 10)) / 1000 // a 10 years after the last day in the range
          })
        }
      }else{
          await createReservation({
            item_id: item,
            from: (new Date(toDateData).getTime() + 24 * 60 * 60 * 1000) / 1000, // a day after the last day in the range
            to: (new Date(toDateData).getTime() + 24 * 60 * 60 * 1000 * (365 * 10)) / 1000 // a 10 years after the last day in the range
        })
      }
    }
    
    const closedDays = findClosedDates(closedDates, fromDateData, toDateData)
    const timeDiff = Math.abs(new Date(toDateData) - new Date(fromDateData))
    let diffDays = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1
    diffDays = diffDays - closedDays.length
    const fromDate = new Date(fromDateData)/1000
    const toDate = new Date(toDateData)/1000
    const data = {
      customer_id: selectedText,
      item_id: item,
      from: fromDate,
      to: toDate,
      fromDate: fromDateData,
      toDate: toDateData,
      days: diffDays
  }
    /* const loading = document.querySelector("#loading")
    loading.style.display = "flex" */
    const res = await createBooking(data)
    data.booking_id = res.id

    await createOrder(data)
    /* loading.style.display = "none" */
    alert(res.msg)
    window.location.href = "index.php?singleItem=" + item
  })
}



const init = async () => {
  const queryString = window.location.search
  let urlParams = ""
  if (queryString !== ""){
    urlParams = new URLSearchParams(queryString)
    if(urlParams.has("format")){
      const format = urlParams.get("format")
      document.querySelector(".format").selectedIndex = format
    }
  }
  // search for customers
  const customers = await getAllCustomers()
  const cust = document.querySelector(".customers")
  cust.innerHTML = ""

  customers.forEach(c => {
    let name = ""
    if(settings.search_cust_name != "1" && settings.search_cust_tlf != "1" && settings.search_cust_number != "1"){
      name = c.name
    }
    if(settings.search_cust_name === "1"){
      name = c.name + " "
    }
    if(settings.search_cust_tlf === "1"){
      name += c.phone + " "
    }
    if(settings.search_cust_number === "1"){
      name += c.account_number
    }
    const option = createOptionElementDatalist(c.id, name)
    cust.appendChild(option)
  })

  if(urlParams === "" || !urlParams.has("item")){
    await drawForm()
}

  if(urlParams !== "" && (urlParams.has("day") && urlParams.has("month") && urlParams.has("year"))){
    let day = urlParams.get("day")
    let month = urlParams.get("month")
    const year = urlParams.get("year")
    if(month.length === 1) 
      month = "0" + month
    if(day.length === 1)
      day = "0" + day
    const newDate = year + "-" + month + "-" + day
    document.querySelector("[name='fromDate']").value = newDate
  }else if(urlParams !== "" && urlParams.has("item")){
    const item = urlParams.get("item")
    singleItem(item)
  }
}

init()
})()