<?php

/**
 * This class is intended as a first filter in controllers specifically intended to check incoming api requests
 * for accessTokens and scope checking.
 *
 * Note that a method can be used by more than one scope.  Example shown by 'apiGetSmokeLayer' method.
 *
 * Example use in controller:
 *
 *  public function filters()
 *  {
 *      return array(
 *          'accessControl',
 *          array(
 *              'WDSAPIFilter',
 *              'apiActions' => array(
 *                  WDSAPI::SCOPE_DASH => array(
 *                      'apiGetMapLayer',
 *                      'apiGetAttachment',
 *                      'apiGetSmokeLayer'
 *                  ),
 *                  WDSAPI::SCOPE_RISK => array(
 *                      'apiGetSmokeLayer'
 *                  )
 *              )
 *          )
 *      );
 *  }
 *
 * @author Matt Eiben
 */
class WDSAPIFilter extends CFilter
{
    /**
     * Array of scoped controller actions to filter against
     * @var array
     */
    private $_apiActions = array(
        WDSAPI::SCOPE_DASH => array(),
        WDSAPI::SCOPE_FIRESHIELD => array(),
        WDSAPI::SCOPE_RISK => array(),
        WDSAPI::SCOPE_USAAENROLLMENT => array(),
        WDSAPI::SCOPE_ENGINE => array(),
        WDSAPI::WDS_PRO => array()
    );

    /**
     * Array of availible scopes to use
     * @var array
     */
    private $_availibleScopes = array(
        WDSAPI::SCOPE_DASH,
        WDSAPI::SCOPE_FIRESHIELD,
        WDSAPI::SCOPE_RISK,
        WDSAPI::SCOPE_USAAENROLLMENT,
        WDSAPI::SCOPE_ENGINE,
        WDSAPI::WDS_PRO
    );

	/**
     * Performs the pre-action filtering.
     * @param CFilterChain $filterChain the filter chain that the filter is on.
     * @return boolean whether the filtering process should continue and the action
     * should be executed.
     */
    protected function preFilter($filterChain)
    {
        $action = $filterChain->controller->action->getId();
        $controllerId = $filterChain->controller->getId();

        // Checking if action is api method or controlled id is "api"

        if (substr($action, 0, 3) === 'api' || $controllerId === 'api')
        {
            // Checking to make sure array is configued properly

            $actionConfigured = false;
            $actionConfiguredScopeArray = array();

            // All of the following CExceptions thrown should be caught in development, as they are intended for catching bad configuations
            foreach ($this->apiActions as $scope => $actions)
            {
                // Is scope one of the predefined scopes?
                if (in_array($scope, $this->availibleScopes, true) === false)
                    throw new CException(Yii::t('yii', '{filter} parameter "apiActions" was not configued correctly in {controller}.', array('{filter}' => get_class($this), '{controller}' => get_class($filterChain->controller))));

                // Does the action match of the configured actions?
                if (is_array($actions) && $actions && in_array($action, $actions, true) === true)
                {
                    $actionConfigured = true;
                    $actionConfiguredScopeArray[] = $scope;
                }
            }

            if ($actionConfigured === false)
                throw new CException(Yii::t('yii', '"{action}" was not configured in {filter}.', array('{action}' => $action, '{filter}' => get_class($this))));

            // Everything is filtered correctly, now check access token and scope again DB

            if (!WDSAPI::checkAccessToken(implode(' ', $actionConfiguredScopeArray)))
                return false;
        }

        return $filterChain->run();
    }

    /**
     * Sets the actions to filter against
     * @param callable $value
     */
    public function setApiActions($value)
    {
        $this->_apiActions = $value;
    }

    /**
     * Gets the api actions 2D array set in the configuration
     * @return callable
     */
    public function getApiActions()
    {
        return $this->_apiActions;
    }

    /**
     * Gets the total number of scopes to check against
     * @return callable
     */
    public function getAvailibleScopes()
    {
        return $this->_availibleScopes;
    }
}