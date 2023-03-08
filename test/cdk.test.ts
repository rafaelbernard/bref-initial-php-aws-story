import * as cdk from 'aws-cdk-lib';
import { Template } from 'aws-cdk-lib/assertions';
import * as Cdk from '../cdk/cdk-stack';

// example test. To run these tests, uncomment this file along with the
// example resource in lib/cdk-stack.ts
test('Lambda created', () => {
  const app = new cdk.App();
    // WHEN
  const stack = new Cdk.CdkStack(app, 'MyTestStack');
    // THEN
  const template = Template.fromStack(stack);

  template.hasResourceProperties('AWS::Lambda::Function', {
    Layers: [Cdk.CdkStack.brefLayerFunctionArn],
    FunctionName: 'fibonacci-image',
  });
});

test('Bucket created', () => {
  const app = new cdk.App();
  // WHEN
  const stack = new Cdk.CdkStack(app, 'MyTestStack');
  // THEN
  const template = Template.fromStack(stack);

  template.hasResourceProperties('AWS::S3::Bucket', {
    Tags: [{Key: 'aws-cdk:auto-delete-objects', Value: 'true'}],
  });
});
