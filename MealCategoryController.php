<?php

namespace Modules\messManage\Controllers;

use CodeIgniter\HTTP\Response;
use Modules\messManage\Models\CampModel;
use App\Models\MealCategoryModel;

class MealCategoryController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->tokenData = $this->getTokenData(1);
        $roleId = $this->tokenData->role_id;
        if (!in_array($roleId, array(ROLE_KITCHEN_MANAGER))) {
            $response = [
                'status' => Response::HTTP_UNAUTHORIZED,
                'error' => true,
                'message' => lang('Messages.access_denied'),
            ];
            _send_response(Response::HTTP_UNAUTHORIZED, $response);
        }
        $this->module .= ' Meal Category ';
        $this->logData = $this->tokenData;
        $this->logData->module = $this->module;
    }
    /***** MEAL Category **********/
    public function index()
    {
        try {
            $status = stripeSingleQuotes($this->request->getVar('status'));
            $clientId = $this->tokenData->client_id;
            $modelCategory = new MealCategoryModel();
            $responseList = $modelCategory->listAll($clientId, $status);
            if ($responseList) {
                _sendDataClean($responseList, 1);
                $response = [
                    'status' => Response::HTTP_OK,
                    'error' => false,
                    'message' => '',
                    'data' => array("list" => $responseList)
                ];
                $this->logActivity->sendLogSuccess($this->logData);
                return $this->respond($response, Response::HTTP_OK);
            } else {
                $response = [
                    'status' => Response::HTTP_OK,
                    'error' => true,
                    'message' => 'No record available',
                ];
                return $this->respond($response, Response::HTTP_OK);
            }
        } catch (\Exception $ex) {
            $response = [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'error' => true,
                'messages' => lang('Messages.error_someting_went_wrong') . $ex->getMessage(),
                'data' => []
            ];
            return $this->respond($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id = null)
    {
        $clientId = $this->tokenData->client_id;
        $modelCategory = new MealCategoryModel();
        $responseList = $modelCategory->getById($id, $clientId);
        if ($responseList) {
            _sendDataClean($responseList);
            $response = [
                'status' => Response::HTTP_OK,
                'error' => true,
                'message' => '',
                'data' => array("list" => $responseList)
            ];
            $this->logActivity->sendLogSuccess($this->logData, ['id' => $id]);
            return $this->respond($response, Response::HTTP_OK);
        } else {
            $response = [
                'status' => Response::HTTP_OK,
                'error' => true,
                'message' => 'No record available',
            ];
            return $this->respond($response, Response::HTTP_OK);
        }
    }


    public function create()
    {
        try {
            $clientId = $this->tokenData->client_id;
            //Validation Rules
            $rules = [
                'category_name' => 'required|is_unique[meal_categories.category_name]'
            ];
            //Validation Messages
            $messages = [
                "category_name" => [
                    "required" => "Category name is required",
                    "is_unique" => "Category name field must contain a unique value"
                ]
            ];
            //Check Validation
            if (!$this->validate($rules, $messages)) {
                $response = [
                    'status' => Response::HTTP_BAD_REQUEST,
                    'error' => true,
                    'message' => $this->validator->getErrors(),
                ];
                return $this->respond($response, Response::HTTP_BAD_REQUEST);
            } else {
                $modelCategory = new MealCategoryModel();
                $crudData = array(
                    "client_id" => $clientId,
                    "category_name" => stripeSingleQuotes($this->request->getVar('category_name')),
                    "category_status" => $modelCategory->status['ACTIVE']
                );

                $responseInsert = $modelCategory->insertRecord($crudData);
                if ($responseInsert) {
                    $response = [
                        'status' => Response::HTTP_OK,
                        'error' => false,
                        'messages' => 'Category created successfully',
                    ];
                    $this->logActivity->sendLogSuccess($this->logData);
                    return $this->respond($response, Response::HTTP_OK);
                } else {
                    $response = [
                        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                        'error' => true,
                        'messages' => 'Category could not be created',
                    ];
                    $this->logActivity->sendLogError($this->logData);
                    return $this->respond($response, Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        } catch (\Exception $e) {
            $response = [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'error' => true,
                'messages' => lang('Messages.error_someting_went_wrong') . $e->getMessage(),
            ];
            return $this->respond($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($id = null)
    {
        try {
            $clientId = $this->tokenData->client_id;
            //Validation Rules
            $rules = [
                'category_name' => "required|isUniqueValueUpdate[category_name|" . $id . "|meal_categories]",
            ];
            //Validation Messages
            $messages = [
                "category_name" => [
                    "required" => "Category name is required",
                    "isUniqueValueUpdate" => "Category name field must contain a unique value."
                ]
            ];
            //Check Validation
            if (!$this->validate($rules, $messages)) {
                $response = [
                    'status' => Response::HTTP_BAD_REQUEST,
                    'error' => true,
                    'message' => $this->validator->getErrors(),
                ];
                return $this->respond($response, Response::HTTP_BAD_REQUEST);
            } else {
                $modelCategory = new MealCategoryModel();
                $responseCategory = $modelCategory->getById($id, $clientId);
                if (empty($responseCategory)) {
                    $response = [
                        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                        'error' => true,
                        'messages' => 'Record not available',
                    ];
                    return $this->respond($response, Response::HTTP_INTERNAL_SERVER_ERROR);
                } else {
                    $crudData = array(
                        "category_name" => stripeSingleQuotes($this->request->getVar('category_name')),
                        "updated_at" => _getCurrentDateTime()
                    );
                    $where = array("id" => $id);
                    $responseInsert = $modelCategory->updateRecord($crudData, $where);
                    if ($responseInsert) {
                        $response = [
                            'status' => Response::HTTP_OK,
                            'error' => false,
                            'messages' => 'Category updated successfully',
                        ];
                        $this->logActivity->sendLogSuccess($this->logData);
                        return $this->respond($response, Response::HTTP_OK);
                    } else {
                        $response = [
                            'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                            'error' => true,
                            'messages' => 'Category could not be updated',
                        ];
                        $this->logActivity->sendLogError($this->logData);
                        return $this->respond($response, Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
            }
        } catch (\Exception $e) {
            $response = [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'error' => true,
                'messages' => lang('Messages.error_someting_went_wrong') . $e->getMessage(),
            ];
            return $this->respond($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete($id = null)
    {
        $clientId = $this->tokenData->client_id;
        $modelCategory = new MealCategoryModel();
        $responseCategory = $modelCategory->getById($id, $clientId);
        if ($responseCategory) {
            $modelCategory->delete($id);
            $response = [
                'status' => Response::HTTP_OK,
                'error' => true,
                'message' => 'Category deleted successfully',
            ];
            $this->logActivity->sendLogSuccess($this->logData, ['id' => $id]);
            return $this->respond($response, Response::HTTP_OK);
        } else {
            $response = [
                'status' => Response::HTTP_OK,
                'error' => true,
                'message' => 'No record available',
            ];
            return $this->respond($response, Response::HTTP_OK);
        }
    }
}
