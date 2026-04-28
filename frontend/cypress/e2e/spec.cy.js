describe('Spec', () => {
  it('loads homepage', () => {
    cy.visit('/')
    cy.contains('Book').should('exist')
  })
})
