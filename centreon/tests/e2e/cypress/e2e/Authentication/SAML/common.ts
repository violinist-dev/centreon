/* eslint-disable cypress/unsafe-to-chain-command */
const keycloakURL = `${Cypress.env('OPENID_IMAGE_URL')}/realms/Centreon_SSO`;

const SAMLConfigValues = {
  entityID: keycloakURL,
  loginAttribute: 'urn:oid:1.2.840.113549.1.9.1', // email
  logoutURL: `${keycloakURL}/protocol/saml`,
  remoteLoginURL: `${keycloakURL}/protocol/saml/clients/centreon`,
  x509Certificate:
    'MIICpzCCAY8CBgGFydyVcDANBgkqhkiG9w0BAQsFADAXMRUwEwYDVQQDDAxDZW50cmVvbl9TU08wHhcNMjMwMTE5MTE0NzM0WhcNMzMwMTE5MTE0OTE0WjAXMRUwEwYDVQQDDAxDZW50cmVvbl9TU08wggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCCpNndecGJI2xOaNQXDDvwDwo/beQ7Q4HW/ck1BNkE13IgPf5GRpvP2jp/1IZsx92vQ2Ub9g5urNG/jo3nZzsUUIdTICsN9Bq2OIjYU9Uxmc1PpHzklN/SqZWbKXOw8EzqXkQ3YNXHqL9omJJ5JMxe4zg758zlvOUh3I44XhMy6PKgeReJIm+HxYJ8SKeu/XVRI7Uiyav5L2M85ED3kqiI3iPrGfLQzv8zqkTeNfuZIeigqI+M8MqRxR3Qf0UlmWA3ZAzsoxJUU+e0tHnD7MhgyRLfg76FjQ1U7Tv7X/h8uqRthjTbva5v0k0M85z21C85UrHxpS3e/HJFInrkJredAgMBAAEwDQYJKoZIhvcNAQELBQADggEBADQANd/iYhefXpcqXC+co3fEe7IaZ93XelZzJ5S4OAR5dHnhMMlMQnnscW/nH8NAEwWRImJPfOEcKun8rBUphZZJxi2WHHj5ilhGdNtcyZzh0sufyIQav/QMreGmDEj/J/uRfmG15Lj1wJB6mw+O4kuwJj/8DzxK6/sQYPisJuXrSWrDmcpvShvbo59JbVjdYK49WXVDbl++7hrwiOYuCQ/uodQYgvChZnIQbL4O6TbG4OLy+prFd5FBsEQds8ZNXoLWM5bCUz+bz4N68fAqhtPR8+yR+pIrE7/cvRaRCmgnG0s61JBZVxHoT4dbMJUTTSSS4dWCUUNhMCIFtEKL06c='
};

const configureSAML = (): Cypress.Chainable => {
  cy.getByLabel({ label: 'Remote login URL', tag: 'input' }).type(
    SAMLConfigValues.remoteLoginURL,
    { force: true }
  );

  cy.getByLabel({ label: 'Issuer (Entity ID) URL', tag: 'input' }).type(
    SAMLConfigValues.entityID,
    { force: true }
  );

  cy.getByLabel({
    label: 'Copy/paste x.509 certificate',
    tag: 'textarea'
  }).type(SAMLConfigValues.x509Certificate, { force: true });

  cy.getByLabel({
    label: 'User ID (login) attribute for Centreon user',
    tag: 'input'
  }).type(SAMLConfigValues.loginAttribute, { force: true });

  cy.getByLabel({
    label: 'Both identity provider and Centreon UI',
    tag: 'input'
  }).check({ force: true });

  return cy
    .getByLabel({ label: 'Logout URL', tag: 'input' })
    .type(SAMLConfigValues.logoutURL, { force: true });
};

const navigateToSAMLConfigPage = (): Cypress.Chainable => {
  cy.navigateTo({
    page: 'Authentication',
    rootItemNumber: 4
  })
    .get('div[role="tablist"] button:nth-child(4)')
    .click();

  cy.wait('@getSAMLProvider');

  return cy.getByLabel({ label: 'Identity provider' }).click();
};

const initializeSAMLUser = (): Cypress.Chainable => {
  return cy
    .fixture('resources/clapi/contact-SAML/SAML-authentication-user.json')
    .then((contact) => cy.executeActionViaClapi({ bodyContent: contact }));
};

const removeContact = (): Cypress.Chainable => {
  return cy.setUserTokenApiV1().then(() => {
    cy.executeActionViaClapi({
      bodyContent: {
        action: 'DEL',
        object: 'CONTACT',
        values: 'oidc'
      }
    });
  });
};

const addSAMLAcl = (): Cypress.Chainable => {
  return cy.setUserTokenApiV1().then(() => {
    cy.executeActionViaClapi({
      bodyContent: {
        action: 'ADD',
        object: 'ACLGROUP',
        values: 'ACL Group test;CYPRESS'
      }
    });
  });
};

export {
  initializeSAMLUser,
  removeContact,
  configureSAML,
  navigateToSAMLConfigPage,
  addSAMLAcl
};
