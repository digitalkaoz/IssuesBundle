/**
 * @jsx React.DOM
 */
var React = require('react');
var Project = require('./project.jsx');

var ProjectsContainer = React.createClass({

    renderProjects: function () {
        var projects = [];
        var sortedProjects = this.sortProjects();

        sortedProjects.map(function (p) {
            var project = this.props.projects[p.key];
            projects.push(<Project key={p.key} filters={this.props.filters} project={project} collapsed={this.props.collapsed[p.key]} issues={this.props.issues[p.key]} ></Project>);
        }.bind(this));

        if (0 === projects.length) {
            projects.push(<div className="panel panel-danger"><div className="panel-heading"><h3>No Projects imported</h3></div><div className="panel-body">did you run "app/console fos:elastica:populate"?</div></div>);
        }

        return projects;
    },

    sortProjects: function () {
        var projects = [];

        var checkFilter = function (key, issue, filters) {
            return filters[key].count() === 0 || filters[key].val().indexOf(issue[key].val()) != -1;
        };

        this.props.projects.forEach(function (key, project) {
            var counter = 0;
            this.props.issues[key].map(function (issue) {
                var authorFilter = checkFilter('author', issue, this.props.filters);
                var assigneeFilter = checkFilter('assignee', issue, this.props.filters);
                var typeFilter = checkFilter('type', issue, this.props.filters);
                var textFilter = this.props.filters.description.val() === null || issue.title.val().toLowerCase().indexOf(this.props.filters.description.val().toLowerCase()) != -1;
                var stateFilter = checkFilter('state', issue, this.props.filters);

                if (authorFilter && assigneeFilter && typeFilter && textFilter && stateFilter) {
                    counter +=1;
                }
            }.bind(this));

            if (0 === counter) {
                counter = project.issuesCount.val();
            }

            projects.push({key: key, counter: counter});
        }.bind(this));

        return projects;

        //return projects.sort(function(a,b) {
        //    if (a.counter > b.counter) {
        //        return 1;
        //    }
        //    if (a.counter < b.counter) {
        //        return -1;
        //    }
        //    return 0;
        //}).reverse();
    },

    render: function () {
        return (
            <div className="content">
                <div className="main">
                    {this.renderProjects()}
                </div>
            </div>
        );
    }
});

module.exports = ProjectsContainer;
