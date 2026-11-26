<?php
require_once __DIR__ . '/database.php';
require_once BASE_PATH . '/model/BaseModel.php';
require_once BASE_PATH . '/model/AuthModel.php';
require_once BASE_PATH . '/model/MenuModel.php';
require_once BASE_PATH . '/model/OrderModel.php';
require_once BASE_PATH . '/model/RewardModel.php';
require_once BASE_PATH . '/model/FinanceModel.php';
require_once BASE_PATH . '/model/InventoryModel.php';
require_once BASE_PATH . '/model/AddressModel.php';
require_once BASE_PATH . '/model/ShippingModel.php';
require_once BASE_PATH . '/model/CartModel.php';
require_once BASE_PATH . '/model/VoucherModel.php';
require_once BASE_PATH . '/model/ChatModel.php';
require_once BASE_PATH . '/model/RecipeModel.php';
require_once BASE_PATH . '/services/RewardService.php';
require_once BASE_PATH . '/services/QrisGateway.php';
require_once BASE_PATH . '/services/Mailer.php';
// Controllers
require_once BASE_PATH . '/controller/AuthController.php';
require_once BASE_PATH . '/controller/OrderController.php';
require_once BASE_PATH . '/controller/AdminController.php';
require_once BASE_PATH . '/controller/ReviewController.php';
require_once BASE_PATH . '/controller/CustomerController.php';
require_once BASE_PATH . '/controller/ChatController.php';
