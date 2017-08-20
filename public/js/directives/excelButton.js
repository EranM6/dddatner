dddatner.directive('excelButtonDirective', [excelButtonDirective]);

function excelButtonDirective() {
    return {
        restrict: 'E',
        template: '' +
            '<button class="btn btn-success pull-left" ng-click="file()">' +
                '<img src="https://cdn3.iconfinder.com/data/icons/document-icons-2/30/647708-excel-128.png" style="width: 3rem; margin-left: 1rem;"/>' +
        'הורד לאקסל' +
            '</button>'

    };
}