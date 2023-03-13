import * as cdk from 'aws-cdk-lib';
import { Match, Template } from 'aws-cdk-lib/assertions';
import * as Cdk from '../cdk/cdk-stack';

// example test. To run these tests, uncomment this file along with the
// example resource in lib/cdk-stack.ts
describe('Testing the template', () => {
  const app = new cdk.App();
  // WHEN
  const stack = new Cdk.CdkStack(app, 'MyTestStack');
  // THEN
  const template = Template.fromStack(stack);

  it('test', () => {
    template.hasResourceProperties('AWS::Lambda::Function', {
      Layers: [Cdk.CdkStack.brefLayerFunctionArn],
      FunctionName: 'fibonacci-image',
    });
  });

  it('Should have an S3 Bucket', () => {
    template.hasResourceProperties('AWS::S3::Bucket', {
      Tags: [{ Key: 'aws-cdk:auto-delete-objects', Value: 'true' }],
    });
  });

  it('Should have a policy for S3', () => {
    template.hasResourceProperties('AWS::IAM::Policy', {
      PolicyName: Match.stringLikeRegexp("^BrefStoryGetFunctionServiceRoleDefaultPolicy"),
      PolicyDocument: {
        Statement: [{
          Action: [
            "s3:GetObject*",
            "s3:GetBucket*",
            "s3:List*",
            "s3:DeleteObject*",
            "s3:PutObject",
            "s3:PutObjectLegalHold",
            "s3:PutObjectRetention",
            "s3:PutObjectTagging",
            "s3:PutObjectVersionTagging",
            "s3:Abort*",
          ],
        }],
      },
    });
  });
});
