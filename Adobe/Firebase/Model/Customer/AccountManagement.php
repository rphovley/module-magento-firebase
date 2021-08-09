<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Adobe\Firebase\Model\Customer;

use Adobe\Firebase\Model\Auth as FireBaseAuth;

/**
 * Class AccountManagement
 * Adobe\Firebase\Model\Customer
 */
class AccountManagement extends \Magento\Customer\Model\AccountManagement
{
    /**
     * Configuration paths for create account email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'customer/create_account/email_template';

    /**
     * Configuration paths for register no password email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE = 'customer/create_account/email_no_password_template';

    /**
     * Configuration paths for remind email identity
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_REGISTER_EMAIL_IDENTITY = 'customer/create_account/email_identity';

    /**
     * Configuration paths for remind email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_REMIND_EMAIL_TEMPLATE = 'customer/password/remind_email_template';

    /**
     * Configuration paths for forgot email email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'customer/password/forgot_email_template';

    /**
     * Configuration paths for forgot email identity
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'customer/password/forgot_email_identity';

    /**
     * Configuration paths for account confirmation required
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see AccountConfirmation::XML_PATH_IS_CONFIRM
     */
    const XML_PATH_IS_CONFIRM = 'customer/create_account/confirm';

    /**
     * Configuration paths for account confirmation email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'customer/create_account/email_confirmation_template';

    /**
     * Configuration paths for confirmation confirmed email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_CONFIRMED_EMAIL_TEMPLATE = 'customer/create_account/email_confirmed_template';

    /**
     * Constants for the type of new account email to be sent
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED = 'registered';

    /**
     * Welcome email, when password setting is required
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD = 'registered_no_password';

    /**
     * Welcome email, when confirmation is enabled
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMATION = 'confirmation';

    /**
     * Confirmation email, when account is confirmed
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMED = 'confirmed';

    /**
     * Constants for types of emails to send out.
     * pdl:
     * forgot, remind, reset email templates
     */
    const EMAIL_REMINDER = 'email_reminder';

    const EMAIL_RESET = 'email_reset';

    /**
     * Configuration path to customer password minimum length
     */
    const XML_PATH_MINIMUM_PASSWORD_LENGTH = 'customer/password/minimum_password_length';

    /**
     * Configuration path to customer password required character classes number
     */
    const XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER = 'customer/password/required_character_classes_number';

    /**
     * Configuration path to customer reset password email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see Magento/Customer/Model/EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_RESET_PASSWORD_TEMPLATE = 'customer/password/reset_password_template';

    /**
     * Minimum password length
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see \Magento\Customer\Model\AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH
     */
    const MIN_PASSWORD_LENGTH = 6;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Customer\Api\Data\ValidationResultsInterfaceFactory
     */
    private $validationResultsDataFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadataService;

    /**
     * @var PsrLogger
     */
    protected $logger;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var ConfigShare
     */
    private $configShare;

    /**
     * @var StringHelper
     */
    protected $stringHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var CustomerViewHelper
     */
    protected $customerViewHelper;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var CustomerModel
     */
    protected $customerModel;

    /**
     * @var AuthenticationInterface
     */
    protected $authentication;

    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var \Magento\Eav\Model\Validator\Attribute\Backend
     */
    private $eavValidator;

    /**
     * @var CredentialsValidator
     */
    private $credentialsValidator;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var AccountConfirmation
     */
    private $accountConfirmation;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * @var AllowedCountries
     */
    private $allowedCountriesReader;

    /**
     * @var GetCustomerByToken
     */
    private $getByToken;

    /**
     * @var SessionCleanerInterface
     */
    private $sessionCleaner;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var Auth
     */
    private $_firebaseAuth;

    /**
     * AccountManagement constructor.
     * @param FireBaseAuth $firebaseAuth
     * @param CustomerFactory $customerFactory
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Random $mathRandom
     * @param Validator $validator
     * @param ValidationResultsInterfaceFactory $validationResultsDataFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerMetadataInterface $customerMetadataService
     * @param CustomerRegistry $customerRegistry
     * @param PsrLogger $logger
     * @param Encryptor $encryptor
     * @param ConfigShare $configShare
     * @param StringHelper $stringHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param DataObjectProcessor $dataProcessor
     * @param Registry $registry
     * @param CustomerViewHelper $customerViewHelper
     * @param DateTime $dateTime
     * @param CustomerModel $customerModel
     * @param ObjectFactory $objectFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param CredentialsValidator|null $credentialsValidator
     * @param DateTimeFactory|null $dateTimeFactory
     * @param AccountConfirmation|null $accountConfirmation
     * @param SessionManagerInterface|null $sessionManager
     * @param SaveHandlerInterface|null $saveHandler
     * @param CollectionFactory|null $visitorCollectionFactory
     * @param SearchCriteriaBuilder|null $searchCriteriaBuilder
     * @param AddressRegistry|null $addressRegistry
     * @param GetCustomerByToken|null $getByToken
     * @param AllowedCountries|null $allowedCountriesReader
     * @param SessionCleanerInterface|null $sessionCleaner
     * @param AuthorizationInterface|null $authorization
     */
    public function __construct(
        FireBaseAuth $firebaseAuth,
        CustomerFactory $customerFactory,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Random $mathRandom,
        Validator $validator,
        ValidationResultsInterfaceFactory $validationResultsDataFactory,
        AddressRepositoryInterface $addressRepository,
        CustomerMetadataInterface $customerMetadataService,
        CustomerRegistry $customerRegistry,
        PsrLogger $logger,
        Encryptor $encryptor,
        ConfigShare $configShare,
        StringHelper $stringHelper,
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        DataObjectProcessor $dataProcessor,
        Registry $registry,
        CustomerViewHelper $customerViewHelper,
        DateTime $dateTime,
        CustomerModel $customerModel,
        ObjectFactory $objectFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        CredentialsValidator $credentialsValidator = null,
        DateTimeFactory $dateTimeFactory = null,
        AccountConfirmation $accountConfirmation = null,
        SessionManagerInterface $sessionManager = null,
        SaveHandlerInterface $saveHandler = null,
        CollectionFactory $visitorCollectionFactory = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        AddressRegistry $addressRegistry = null,
        GetCustomerByToken $getByToken = null,
        AllowedCountries $allowedCountriesReader = null,
        SessionCleanerInterface $sessionCleaner = null,
        AuthorizationInterface $authorization = null
    ){
        $this->_firebaseAuth = $firebaseAuth;
        $this->customerFactory = $customerFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->mathRandom = $mathRandom;
        $this->validator = $validator;
        $this->validationResultsDataFactory = $validationResultsDataFactory;
        $this->addressRepository = $addressRepository;
        $this->customerMetadataService = $customerMetadataService;
        $this->customerRegistry = $customerRegistry;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->configShare = $configShare;
        $this->stringHelper = $stringHelper;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->dataProcessor = $dataProcessor;
        $this->registry = $registry;
        $this->customerViewHelper = $customerViewHelper;
        $this->dateTime = $dateTime;
        $this->customerModel = $customerModel;
        $this->objectFactory = $objectFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $objectManager = ObjectManager::getInstance();
        $this->credentialsValidator =
            $credentialsValidator ?: $objectManager->get(CredentialsValidator::class);
        $this->dateTimeFactory = $dateTimeFactory ?: $objectManager->get(DateTimeFactory::class);
        $this->accountConfirmation = $accountConfirmation ?: $objectManager
            ->get(AccountConfirmation::class);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder
            ?: $objectManager->get(SearchCriteriaBuilder::class);
        $this->addressRegistry = $addressRegistry
            ?: $objectManager->get(AddressRegistry::class);
        $this->getByToken = $getByToken
            ?: $objectManager->get(GetCustomerByToken::class);
        $this->allowedCountriesReader = $allowedCountriesReader
            ?: $objectManager->get(AllowedCountries::class);
        $this->sessionCleaner = $sessionCleaner ?? $objectManager->get(SessionCleanerInterface::class);
        $this->authorization = $authorization ?? $objectManager->get(AuthorizationInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function authenticate($username, $password)
    {
        try {
            $customer = $this->customerRepository->get($username);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        $customerId = $customer->getId();
        if(!$this->_firebaseAuth->isGoogleFBAuthenticationEnabled()){
            if ($this->getAuthentication()->isLocked($customerId)) {
                throw new UserLockedException(__('The account is locked.'));
            }
        }
        try {
            if($this->_firebaseAuth->isGoogleFBAuthenticationEnabled()){
                $result = $this->_firebaseAuth->loginWithFirebase($username, $password);
                if(!$result){
                    throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
                }
            }else {
                $this->getAuthentication()->authenticate($customerId, $password);
            }
        } catch (InvalidEmailOrPasswordException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        if ($customer->getConfirmation() && $this->isConfirmationRequired($customer)) {
            throw new EmailNotConfirmedException(__("This account isn't confirmed. Verify and try again."));
        }

        $customerModel = $this->customerFactory->create()->updateData($customer);
        $this->eventManager->dispatch(
            'customer_customer_authenticated',
            ['model' => $customerModel, 'password' => $password]
        );

        $this->eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);

        return $customer;
    }
}
