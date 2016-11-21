import datetime
from io import StringIO
import logging
from lxml import etree
import os
import re
import sys

from common import get_all_versions

logger = logging.getLogger(__name__)


class Validate_IATI_XML():
    # Set object defaults
    status_overall = None # Overall status of the test itself
    status = {
        'status_well_formed_xml': "Not checked",
        'status_schema': "Not checked"
    } # Output for the status of each test
    xml_raw = {
        'xml': "", # XML input string
        'etree_obj': None # lxml etree object
        }
    xml_pretty = {
        'xml': "", # 'Prettified' (i.e. formatted) version of the raw XML
        'etree_obj': None, # lxml etree object
        'xpath_map': {} # Dict of xpaths to line number
        }
    iati_version = {
        'version_tested': None,
        'type': None
        } # IATI version that self.xml_raw['xml'] will be validated against
    errors = [] # Empty list for any errors that are found
    warnings = []
    start_time = None
    completed_time = None


    def __init__(self, xml=None, iati_version=None):
        logger.info("__init__() method")
        logger.debug('xml={}'.format(xml))
        logger.debug('iati_version={}'.format(iati_version))

        self.errors = []
        self.start_time = datetime.datetime.now()
        if xml is not None:
            self.xml_raw['xml'] = xml

        # Perform well-formed check
        if self.validate_well_formed(): # Will set self.xml_raw['etree_obj'] if successful
            # Continue to validate if the data is well formed

            if iati_version is None:
                version = self.get_version()
                self.iati_version['version_tested'] = version['version']
                self.iati_version['type'] = version['type']
            else:
                self.iati_version['version_tested'] = iati_version
                self.iati_version['type'] = "Input"

            self.validate_schema()

        # Set final output data, based on results
        self.completed_time = datetime.datetime.now()
        self.status_overall = 'Pass' if all(
            [True if v=='Pass' else False for v in self.status.values()]
            ) else 'Fail'
        return


    def prettified_xml_loader(self):
        self.xml_pretty['xml'] = etree.tostring(self.xml_raw['etree_obj'], pretty_print=True)


    def get_version(self):
        # FIXME - Check user-submitted version number is in the Version codelist
        """
        Attempt to get the version number specified in iati-activities/@version.
        If no version present, set as the latest version number.
        Returns:
          Dict containing data with the version found
        """
        logger.info("get_version() method")
        detected_version = self.xml_raw['etree_obj'].xpath('//@version')
        out = {}
        if detected_version:
            out['version'] = detected_version[0]
            out['type'] = "Detected"
        else:
            out['version'] = max(get_all_versions())
            out['type'] = "Version not found, set to latest version."
        return out


    def validate(self):
        """
        METHOD NOT CALLED: Candidate for deletion
        """
        logger.info("validate() method")
        well_formed = Validate_well_formed(self.xml_raw['xml'])
        return True


    def get_metadata(self):
        logger.info("get_metadata() method")
        return {
            'file_size_bytes': sys.getsizeof(self.xml_raw['xml']),
            'began': self.start_time,
            'completed': self.completed_time,
            'version': self.iati_version,
        }


    def get_response(self):
        """
        Get the full results of the validation.
        Returns:
          Dictionary containing reponse data.
        """
        return {
            'metadata': self.get_metadata(),
            'status': {
                'status_overall': self.status_overall,
                'status_detail': self.status
                },
            'error_count': len(self.errors),
            'error_detail': self.errors
            }


    def validate_well_formed(self):
        """
        Specific methods for validating that an XML file can be read and contains no syntax errors
        Returns:
          True -- if successfully set-up an etree object
          False -- if failed
        """
        logger.info("validate_well_formed() method")
        try:
            self.xml_raw['etree_obj'] = etree.fromstring(self.xml_raw['xml'].encode('utf-16'))
            self.status['status_well_formed_xml'] = "Pass"
            logger.info("Passed well-formed check")
            return True

        except etree.XMLSyntaxError as exception_obj:
            # exception_obj = XMLSyntaxError('Premature end of data in tag reporting-org line 2, line 6, column 1',)
            self.status['status_well_formed_xml'] = "Fail"
            logger.info("Failed well-formed check")
            self.errors.append(self.well_formed_error_handler(exception_obj))
            return False


    def well_formed_error_handler(self, exception_obj):
        """
        Return a dict. Uses regex to capture line numbers from the exception message.
        Input:
          exception_obj -- An exception object.
          Example: "Premature end of data in tag iati-activity line 3, line 93, column 1"
        Returns:
          A dict containing lineno, error type, xml context and detailed error message
        """

        """
        NOTE:
        Raw libxml logs are available using exception_obj.error_log.filter_from_level(etree.ErrorLevels.FATAL)
        However the data from these appears to be more confusing in some cases than the overall error message returned in the exception.
        """
        logger.info("well_formed_error_handler() method")
        logger.debug('exception_obj={}'.format(exception_obj))

        # Capture data using regex
        iati_identifier = "" #TODO: Could add regex: Before last <iati-activity element, capture text witin <iati-identifier>...</iati-identifier>
        element = re.findall(r'tag\s(\S+)', str(exception_obj))
        line_number = re.findall(r'line\s(\d+)', str(exception_obj))
        xml_context = "" #TODO: Could add regex: get opening tag of name 'element' and closing tag at second line and column number

        return {
            'type': 'well_formed_error',
            'iati-identifier': None,
            'narrative': str(exception_obj),
            'element': element[0] if element else None,
            'line_number': line_number[0] if line_number else None,
            'xml_context': None
            }


    def validate_schema(self, schema_type="activity"):
        """
        Validate XML against the IATI schema.
        Input:
          schema_type -- The IATI schema to be tested. Should be "activity" or "organisation".
        Returns:
          True -- if successfully set-up an etree object
          False -- if failed
        """
        logger.info("validate_schema() method")
        logger.debug('schema_type={}'.format(schema_type))

        # Determine the path to the schema
        if schema_type == "organisation":
            schema_path =  "{}/iati-schemas/{}/iati-organisations-schema.xsd".format(
                os.path.abspath(os.path.dirname(__file__)),
                self.iati_version['version_tested']
                )
        else:
            schema_path = "{}/iati-schemas/{}/iati-activities-schema.xsd".format(
                os.path.abspath(os.path.dirname(__file__)),
                self.iati_version['version_tested']
                )

        # Load the activity schema
        xmlschema_doc = etree.parse(schema_path)
        xmlschema = etree.XMLSchema(xmlschema_doc)

        # Attempt to validate
        try:
            xmlschema.assertValid(self.xml_raw['etree_obj'])
            self.status['status_schema'] = "Pass"
            logger.info("Passed schema check")
            return True

        except etree.DocumentInvalid as exception_obj:
            self.status['status_schema'] = "Fail"
            logger.info("Failed schema check")
            for error in exception_obj.error_log:
                self.errors.append(self.schema_error_handler(error))
            return False


    def schema_error_handler(self, error):
        """
        Return a dict containing details of the schema error.
        Input:
          error -- A line of the error_log of an etree.DocumentInvalid object.
        Returns:
          A dict containing lineno, error type, xml context and detailed error narrative.
        """
        logger.info("schema_error_handler() method")
        logger.debug('error={}'.format(error))

        return {
                'type': 'schema_error',
                'iati-identifier': None,
                'narrative': error.message,
                'element': None,
                'line_number': error.line,
                'column': error.column,
                'xml_context': None
                }
