import './scss/app.scss'

import 'plasticode-admin'
import {warPlayground} from "./components/war-playground/war-playground.component";

const dependencies = [
    'plasticodeAdmin'
];

const warAdmin = angular.module('warcryAdmin', dependencies)
    .value('API', API_ENDPOINT ? API_ENDPOINT : '/')
    .component('warPlayground', warPlayground);