import { Template } from 'aws-cdk-lib/assertions';
import { expect as expectCDK, haveResource, ResourcePart, SynthUtils } from '@aws-cdk/assert';
import { BrefStack } from "./bref-stack";
import { App } from "aws-cdk-lib";

process.env.CDK_DEFAULT_ACCOUNT = '123456789';
process.env.CDK_DEFAULT_REGION = 'us-east-1';

// example test. To run these tests, uncomment this file along with the
// example resource in lib/cdk-stack.ts
describe('Testing the template', () => {
  const functionName = 'GetFibonacciImage';

  const app = new App();

  const stack = new BrefStack(app, 'MyTestStack', {
    env: {
      account: process.env.CDK_DEFAULT_ACCOUNT,
      region: process.env.CDK_DEFAULT_REGION,
    },
    stackName: 'bref-initial-php-aws-history-cdk',
  });

  const template = Template.fromStack(stack);

  test('Entire stack', () => {
    expect.assertions(1);
    const cfn = SynthUtils.toCloudFormation(stack);
    const resources = cfn.Resources;
    const matchObject: { Parameters: Record<string, unknown>; Resources: Record<string, unknown> } = {
      Parameters: expect.any(Object),
      Resources: {},
    };
    Object.keys(resources).forEach((res) => {
      switch (resources[res].Type) {
        case 'AWS::Lambda::Function':
          matchObject.Resources[res] = {
            Properties: { Code: expect.any(Object) },
          };
          break;
        case 'AWS::Lambda::LayerVersion':
          matchObject.Resources[res] = {
            Properties: {
              Content: expect.any(Object),
            },
          };
          break;
        default:
          break;
      }
    });
    expect(template.toJSON()).toMatchSnapshot(matchObject);
  });

  it('Should have a lambda function to get fibonacci', () => {
    template.hasResourceProperties('AWS::Lambda::Function', {
      FunctionName: functionName,
    });
  });

  it('Should have an S3 Bucket', () => {
    template.hasResourceProperties('AWS::S3::Bucket', {
      Tags: [{ Key: 'aws-cdk:auto-delete-objects', Value: 'true' }],
    });
  });

  // it('Should have a policy for S3', () => {
  //   template.hasResourceProperties('AWS::IAM::Policy', {
  //     PolicyName: Match.stringLikeRegexp(`^${stackPrefix}${functionName}ServiceRoleDefaultPolicy`),
  //     PolicyDocument: {
  //       Statement: [{
  //         Action: [
  //           "s3:GetObject*",
  //           "s3:GetBucket*",
  //           "s3:List*",
  //           "s3:DeleteObject*",
  //           "s3:PutObject",
  //           "s3:PutObjectLegalHold",
  //           "s3:PutObjectRetention",
  //           "s3:PutObjectTagging",
  //           "s3:PutObjectVersionTagging",
  //           "s3:Abort*",
  //         ],
  //       }],
  //     },
  //   });
  // });

  it('Should have DynamoDB', () => {
    expectCDK(stack).to(
      haveResource(
        'AWS::DynamoDB::Table',
        {
          "DeletionPolicy": "Delete",
          "Properties": {
            "AttributeDefinitions": [
              {
                "AttributeName": "PK",
                "AttributeType": "S",
              },
              {
                "AttributeName": "SK",
                "AttributeType": "S",
              },
            ],
            "KeySchema": [
              {
                "AttributeName": "PK",
                "KeyType": "HASH",
              },
              {
                "AttributeName": "SK",
                "KeyType": "RANGE",
              },
            ],
            "ProvisionedThroughput": {
              "ReadCapacityUnits": 5,
              "WriteCapacityUnits": 5,
            },
            "TableName": "BrefStory-table",
          },
          "Type": "AWS::DynamoDB::Table",
          "UpdateReplacePolicy": "Delete",
        },
        ResourcePart.CompleteDefinition,
      )
    );
  });
});
