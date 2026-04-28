const customer = {
  email: 'gregory.kunze@example.org',
  password: 'password',
}

const guest = {
  name: 'Test Test',
  email: 'test@barbershop.test',
}

const service = {
  id: 1,
  name: 'Classic Haircut',
  description: 'Clean haircut and styling',
}

const barber = {
  id: 7,
  name: 'Test Barber',
  total_duration: 30,
}

const bookingTime = '09:00'

function todayString() {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

function mockBookingApi(bookingDate) {
  cy.intercept('GET', '**/api/booking/services*', {
    statusCode: 200,
    body: { data: [service] },
  }).as('services')

  cy.intercept('GET', '**/api/booking/employees*', {
    statusCode: 200,
    body: {
      data: [
        {
          employee: barber,
          is_valid: true,
        },
      ],
    },
  }).as('barbers')

  cy.intercept('GET', '**/api/booking/days*', {
    statusCode: 200,
    body: {
      data: [
        {
          date: bookingDate,
          is_bookable: true,
          occupancy_percent: 0,
        },
      ],
    },
  }).as('days')

  cy.intercept('GET', '**/api/booking/slots*', {
    statusCode: 200,
    body: {
      data: [
        {
          date: bookingDate,
          slots: [bookingTime],
        },
      ],
    },
  }).as('slots')

  cy.intercept('GET', '**/api/booking/summary*', {
    statusCode: 200,
    body: {
      data: {
        services: [service],
        total_duration: 30,
        total_price: 5000,
      },
    },
  }).as('summary')
}

function selectAppointment() {
  cy.wait('@services')
  cy.contains('[data-testid="booking-service-option"]', service.name).click()
  cy.contains('button', 'Continue').click()

  cy.wait('@barbers')
  cy.contains('[data-testid="booking-barber-option"]', barber.name).click()

  cy.wait('@slots')
  cy.contains('button', 'Pick Date and Time').click()
  cy.contains('[data-testid="booking-time-slot"]', bookingTime).click()

  cy.wait('@summary')
  cy.contains('Booking Summary').should('be.visible')
}

function fillGuestDetails() {
  cy.get('[data-testid="booking-guest-name"]').type(guest.name)
  cy.get('[data-testid="booking-guest-email"]').type(guest.email)
}

describe('Booking flow', () => {
  beforeEach(() => {
    cy.viewport(1920, 1080)
    cy.clearLocalStorage()
  })

  it('books an appointment while signed in', () => {
    const bookingDate = todayString()

    mockBookingApi(bookingDate)

    cy.intercept('POST', '**/api/appointments', (req) => {
      expect(req.headers.authorization).to.match(/^Bearer /)
      expect(req.body.service_ids).to.include(service.id)
      expect(req.body.employee_id).to.equal(barber.id)
      expect(req.body.appointment_start).to.equal(`${bookingDate} ${bookingTime}`)

      req.reply({ statusCode: 201, body: { data: { id: 101 } } })
    }).as('createAppointment')

    cy.request('POST', 'http://backend.vm1.test/api/login', customer).then((response) => {
      cy.visit('/booking', {
        onBeforeLoad(browserWindow) {
          browserWindow.localStorage.setItem('customer_token', response.body.token)
          browserWindow.localStorage.setItem('customer_user_id', response.body.user.id)
          browserWindow.localStorage.setItem('customer_role', response.body.user.role)
        },
      })
    })

    selectAppointment()
    cy.get('[data-testid="booking-confirm"]').click()

    cy.wait('@createAppointment')
    cy.location('pathname').should('eq', '/confirmation-pending')
  })

  it('books an appointment as a guest', () => {
    const bookingDate = todayString()

    mockBookingApi(bookingDate)

    cy.intercept('POST', '**/api/appointments', (req) => {
      expect(req.headers.authorization).to.be.undefined
      expect(req.body.service_ids).to.include(service.id)
      expect(req.body.employee_id).to.equal(barber.id)
      expect(req.body.appointment_start).to.equal(`${bookingDate} ${bookingTime}`)
      expect(req.body.guest_name).to.equal(guest.name)
      expect(req.body.guest_email).to.equal(guest.email)

      req.reply({ statusCode: 201, body: { data: { id: 102 } } })
    }).as('createAppointment')

    cy.visit('/booking')
    selectAppointment()
    fillGuestDetails()
    cy.get('[data-testid="booking-confirm"]').click()

    cy.wait('@createAppointment')
    cy.location('pathname').should('eq', '/confirmation-pending')
  })

  it('shows an error if the booking API rejects the guest email', () => {
    const bookingDate = todayString()
    const errorMessage = 'Guest email must be a valid email address.'

    mockBookingApi(bookingDate)

    cy.intercept('POST', '**/api/appointments', {
      statusCode: 422,
      body: {
        message: 'The given data was invalid.',
        errors: {
          guest_email: [errorMessage],
        },
      },
    }).as('createAppointment')

    cy.visit('/booking')
    selectAppointment()
    fillGuestDetails()
    cy.get('[data-testid="booking-confirm"]').click()

    cy.wait('@createAppointment')
    cy.contains(errorMessage).should('be.visible')
    cy.location('pathname').should('eq', '/booking')
  })
})
